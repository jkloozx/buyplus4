<?php
namespace Home\Cart;
/**
 * Class Cart 购物车类
 * @package Home\Cart
 */
class Cart
{

    private static $instance;

    private $wareList = [];// 所有购物车中商品信息列表

    /**
     * 获取对象的方法
     * @return Cart
     */
    public static function instance()
    {
        if (! self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __clone()
    {
    }
    public function addWare($goods_id, $product_id=null, $buy_quantity=1)
    {
        // 拼凑的商品的唯一标志
        $key = $goods_id . '-' . (string)$product_id;// 23-, 23-17

        // 判断商品是否买过
        if (isset($this->wareList[$key])) {
            // 商品在购物车中购买过
            // 增加购买数量即可
            $this->wareList[$key]['buy_quantity'] += $buy_quantity;
        } else {
            // 没有买过, 添加商品信息
            $this->wareList[$key] = [
                'goods_id'      => $goods_id,
                'product_id'    => $product_id,
                'buy_quantity'  => $buy_quantity,
            ];
        }
    }


    private function __construct()
    {
        $this->init();// 构造时 初始化
    }
    /**
     * 初始化
     */
    private function init()
    {
        if (session('member')) {
            // 登录, 去数据表中获取购物车信息
            $warelist = M('Cart')->where(['member_id'=>session('member.member_id')])->getField('warelist');
        } else {
            // 未登录
            $warelist = cookie('warelist');
        }
        // 为商品列表属性复制
        $this->wareList = $warelist ? unserialize($warelist) : [];

    }
    public function __destruct()
    {
        $this->save(); // 析构时 持久化
    }
    /**
     * 持久化存储
     */
    private function save()
    {
        // 当前会员是否登录
        if (session('member')) {
            // 登录
            // 当前会员是否已经有购物车了, 一个会员一个购物车
            if ($cart = M('Cart')->where(['member_id'=>session('member.member_id')])->find()) {
                // 会员有购物车
                M('Cart')->warelist = serialize($this->wareList);
                M('Cart')->save();
            } else {

                $data = [
                    'member_id'     => session('member.member_id'),
                    'cart_title'    => '',
                    'warelist'      => serialize($this->wareList),
                ];
                M('Cart')->add($data);
            }

        } else {
            // 未登录
            cookie('warelist', serialize($this->wareList), ['expire'=>30*24*3600]);
        }

    }


    /**
     * 获取购物车信息
     */
    public function getInfo()
    {
        // 商品信息
        // 遍历所有的商品列表, 获取详尽的信息

        foreach($this->wareList as $key=>$ware) {
            // 完善商品信息
            $goods_id = $ware['goods_id'];
            $product_id = $ware['product_id'];// null
            if (is_null($product_id)) {
                // 没有货品
                $nonProduct[] = $goods_id;
            } else {
                // 存在货品
                $hasProduct[] = $product_id;
            }
        }
        // 查询 数据库
        if (!empty($nonProduct)) {
            // 没有货品的商品信息
            $nonProductList = M('Goods')
                ->field('goods_id, image_thumb, price, name')
                ->where(['goods_id'=>['in', $nonProduct]])
                ->select();
        }


        if (!empty($hasProduct)) {
            // 查询存在货品的商品信息
            $hasProductList = M('Goods')
                ->field('g.goods_id, g.image_thumb, g.price, g.name, p.product_id, p.product_price, pd.value price_drift, group_concat(a.attribute_title, ":", ao.option_value) `option`')
                ->alias('g')
                ->join('left join __PRODUCT__ p using(goods_id)')
                ->join('left join __PRICE_DRIFT__ pd using(price_drift_id)')
                ->join('left join __PRODUCT_GOODS_ATTRIBUTE_OPTION__ pgao using(product_id)')
                ->join('left join __GOODS_ATTRIBUTE_OPTION__ gao using(goods_attribute_option_id)')
                ->join('left join __ATTRIBUTE_OPTION__ ao using(attribute_option_id)')
                ->join('left join __ATTRIBUTE__ a using(attribute_id)')
                ->where(['p.product_id' => ['in', $hasProduct]])
                ->group('p.product_id')
                ->select();
        }
        $productList = array_merge(empty($nonProductList) ? [] : $nonProductList, !empty($hasProductList) ? $hasProductList : []);
        // 仅仅是商品信息, 建立一个的对应购物车中的key
        foreach($productList as $product)  {
            $key = $product['goods_id'] . '-' . (isset($product['product_id'])?$product['product_id']:'');
            $rows[$key] = $product;
        }

        // 将购买信息和商品信息合并到一起
        // 同时统计购物车信息
        $cart['total'] = 0;
        $cart['total_quantity'] = 0;
        foreach($this->wareList as $key=>$ware) {//7-2

            // 计算单价
            if (!is_null($ware['product_id'])) {
                switch($rows[$key]['price_drift']) {
                    case '+':
                        $rows[$key]['real_price'] = $rows[$key]['price'] + $rows[$key]['product_price'];
                        break;
                    case '-':
                        $rows[$key]['real_price'] = $rows[$key]['price'] -  $rows[$key]['product_price'];
                        break;
                    case '=':
                        $rows[$key]['real_price'] = $rows[$key]['product_price'];
                        break;
                }
            } else {
                $rows[$key]['real_price'] = $rows[$key]['price'];
            }
            // 计算单品总价
            $rows[$key]['total_price'] = $rows[$key]['real_price'] * $ware['buy_quantity'];

            // 生成商品URL
            $rows[$key]['url'] = U('/goods/' . $rows[$key]['goods_id']);

            $wareList[$key] = array_merge($ware, $rows[$key]);

            // 购物车信息
            $cart['total_quantity'] += $ware['buy_quantity'];
            $cart['total'] += $rows[$key]['total_price'];
        }


        // 购物车统计信息
        return ['wareList'=>$wareList, 'cart'=>$cart];
    }


    public function removeWare($key)
    {
        unset($this->wareList[$key]);
    }

    public function updateWare($key, $buy_quantity)
    {
        $this->wareList[$key]['buy_quantity'] = $buy_quantity;
    }

    /**
     * 会员的登录同步刷新
     */
    public function memberRefresh()
    {
        // 当前会员可能存在商品
        $this->wareList;// 有可能存在数据, 从数据库中获取

        // 再判断, cookie中是否存在商品
        $cookieWareList = cookie('warelist') ? unserialize(cookie('warelist')) : [];

        // 合并数据表中获取的, 与cookie中获取
        // 遍历cookie中的商品, 判断是否在数据库中存在
        foreach($cookieWareList as $key=>$ware) {
            if (isset($this->wareList[$key])) {
                // 存在过,
                // 一: 累加数量即可
//                $this->wareList[$key]['buy_quantity'] += $ware['buy_quantity'];
                // 二: 取最大数量
                if($ware['buy_quantity'] > $this->wareList[$key]['buy_quantity']) {
                    $this->wareList[$key]['buy_quantity'] = $ware['buy_quantity'];
                }
                // 三: 保持会员数量(什么都不做即可)
            } else {
                // 不存在
                $this->wareList[$key] = $ware;
            }
        }

    }



}