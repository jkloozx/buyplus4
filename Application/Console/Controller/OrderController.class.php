<?php

namespace Console\Controller;


class OrderController
{
    public function processAction()
    {
        // 守护执行
        $redis = new \Redis();
        $redis->connect('127.0.0.1', '6379');// CC('redis-host'), CC('redis-port')
        $m_member = M('Member');
        // 守护执行
        while (true) {

            // 读取订单队列中的中待处理订单
            if ( ! $order = $redis->rpop('orderList')) {
                continue ;
            }
            sleep(10);
            // 生成唯一订单
            $order_sn = date('YmdHis') . mt_rand(1000, 9999) . mt_rand(1000, 9999);
            // 开启事务
            M()->startTrans();
            // 检测订单是否合理(库存是否充足0)
            // 遍历所有的商品, 检测库存信息
            $modelProduct = M('Product');
            $modelGoods = M('Goods');
            $orderInfo = unserialize($order);
            $flagQuantity = true;// 假设库存没有问题.
            foreach($orderInfo['cartInfo']['wareList'] as $ware) {
                if (isset($ware['product_id'])) {
                    // 是某个商品的货品, 检测货品的数量是否合理
                    $row = $modelProduct->where(['product_id'=>$ware['product_id'], 'product_quantity'=>['egt', $ware['buy_quantity']]])->find();
                } else {
                    // 不是货品, 仅仅是商品
                    $row = $modelGoods->where(['goods_id'=>$ware['goods_id'], 'quantity'=>['egt', $ware['buy_quantity']]])->find();
                }
                if ( ! $row) {
                    // 找到库存不足的商品
                    // 订单需要 暂停处理., 选择只要一个不足, 则全都不足
                    $flagQuantity = false;// 库存检测失败
                    break;
                }
            }
            // 判断库存标志
            if (! $flagQuantity) {
                // 库存有问题
                // 回滚事务
                M()->rollback();
                // 通知结果即可
                $redis->hSet('orderResult', 'member-'.$orderInfo['member_id'], 'quantity error');

                echo $order_sn, ' Quantity Error', "\n";
                continue; // 继续处理下个订单
            }

            // 生成订单, 扣减库存
            $data = [
                'order_sn' => $order_sn,
                'member_id' => $orderInfo['member_id'],
                'created_at' => time(),
                'total_quantity'    => $orderInfo['cartInfo']['cart']['total_quantity'],
                'total_price'   => $orderInfo['cartInfo']['cart']['total'],
                'order_status_id'   => 1,
                'shipping_status_id'    => 1,
                'payment_status_id' => 1,
                'address_id'    => $orderInfo['address_id'],
                ];
            // 插入订单表
            $order_id = M('Order')->add($data);

            if(! $order_id) {
                // 订单数据插入失败
                M()->rollback();

                // 通知结果即可
                $redis->hSet('orderResult', 'member-'.$orderInfo['member_id'], 'order error');
                continue;
            }

            // 记录订单中的商品
            $rows = [];
            foreach($orderInfo['cartInfo']['wareList'] as $ware) {
                $rows[] = [
                    'order_id'  => $order_id,
                    'goods_id'  => $ware['goods_id'],
                    'product_id'    => isset($ware['product_id']) ? $ware['product_id'] : 0,
                    'buy_quantity'  => $ware['buy_quantity'],
                    'buy_price' => $ware['real_price'],
                ];

                // 扣减库存
                if (isset($ware['product_id'])) {
                    $modelProduct->where(['product_id'=>$ware['product_id']])->setDec('product_quantity', $ware['buy_quantity']);
                } else {
                    $modelGoods->where(['goods_id'=>$ware['goods_id']])->setDec('quantity', $ware['buy_quantity']);
                }
            }
            // 插入订单商品
            M('OrderWare')->addAll($rows);

            // 业务逻辑成功, 提交
            M()->commit();
            // 成功
            // 通知结果即可
            $redis->hSet('orderResult', 'member-'.$orderInfo['member_id'], 'success');
            // 通知结果
            echo $data['order_sn'], 'Success', "\n";

        }



    }

}