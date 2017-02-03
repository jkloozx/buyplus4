<?php


namespace Home\Controller;


use Home\Cart\Cart;
use Think\Controller;

class BuyController extends Controller
{
    /**
     * 添加商品到购物车
     */
    public function addToCartAction()
    {
        // 请求代理(前端), 需要请求的数据
        $goods_id = I('request.goods_id', null);
        $product_id = I('request.product_id', null);
        $buy_quantity = I('request.buy_quantity', 1, 'intval');

        // 调用购物车, 完成购物车添加
        $cart = Cart::instance();// 单例
        $cart->addWare($goods_id, $product_id, $buy_quantity);// 添加购买的商品
//        dump($cart);
        $this->ajaxReturn(['error'=>0]);
    }

    /**
     * 获取购物车中信息
     */
    public function cartInfoAction()
    {
        $cart = Cart::instance();// 单例
        $info = $cart->getInfo();

        $this->ajaxReturn(['error'=>0, 'data'=>$info]);
    }

    public function removeFromCartAction()
    {
        $key = I('request.key', null);
        $cart = Cart::instance();
        $cart->removeWare($key);
        $this->ajaxReturn(['error'=>0]);
    }
    public function updateQuantityAction()
    {
        $key = I('request.key', null);
        $buy_quantity = I('request.buy_quantity', null);
        $cart = Cart::instance();
        $cart->updateWare($key, $buy_quantity);
        $this->ajaxReturn(['error'=>0]);
    }

    public function cartAction()
    {
        $this->display();
    }

    public function checkoutAction()
    {


        $this->display();

    }

    public function shippingListAction()
    {
        $list = M('Shipping')->where(['enabled'=>1])->select();
        foreach($list as $k=>$shipping) {
            // 计算每种运费的价格
            $shippingName = 'Common\Shipping\\' . $shipping['key'];
            $shipping = new $shippingName;
            $list[$k]['price'] = $shipping->price();
        }
        $this->ajaxReturn(['error'=>0, 'data'=>$list?$list:[]]);
    }


    public function orderAction()
    {
        // 登录之后才可以展示
        if (! session('member')) {
            // 记录下, 登录成功后的目标URL
            session('successUrl', ['route' => '/checkout', 'param' => []]);
            // 未登录
            $this->redirect('/login');
        }

        // 加入订单到队列
        $redis = new \Redis();
        $redis->connect('127.0.0.1', '6379');
        $orderInfo = I('post.');
        // 从购物车中获取的 商品信息
        $cart = Cart::instance();
        $orderInfo['cartInfo'] = $cart->getInfo();

        // 会员id
        $orderInfo['member_id'] = session('member.member_id');

        $redis->hSet('orderResult', 'member-'.$orderInfo['member_id'], 'processing');

        $redis->lpush('orderList', serialize($orderInfo));

        $this->ajaxReturn(['error'=>0]);
    }


    public function resultAction()
    {
        // 登录之后才可以展示
        if (! session('member')) {
            // 记录下, 登录成功后的目标URL
            session('successUrl', ['route' => '/checkout', 'param' => []]);
            // 未登录
            $this->redirect('/login');
        }

        $this->display();

    }

    /**
     * 轮询处理
     */
//    public function orderStateAction()
//    {
//        // 登录之后才可以展示
//        if (! session('member')) {
//            // 记录下, 登录成功后的目标URL
//            session('successUrl', ['route' => '/checkout', 'param' => []]);
//            // 未登录
//            $this->redirect('/login');
//        }
//        // 检测订单的状态.
//        // 加入订单到队列
//        $redis = new \Redis();
//        $redis->connect('127.0.0.1', '6379');
//        $data = $redis->hGet('orderResult', 'member-'.session('member.member_id'));
//
//        switch ($data) {
//            case 'success':
//                $this->ajaxReturn(['error'=>0]);
//                break;
//            case 'processing':
//                $this->ajaxReturn(['error'=>2]);
//                break;
//
//            case 'quantity error':
//            case 'order error':
//                $this->ajaxReturn(['error'=>1]);
//                break;
//        }

    /**
     * 长轮询
     */
    public function orderStateAction()
    {
        // 登录之后才可以展示
        if (! session('member')) {
            // 记录下, 登录成功后的目标URL
            session('successUrl', ['route' => '/checkout', 'param' => []]);
            // 未登录
            $this->redirect('/login');
        }
        // 检测订单的状态.
        // 加入订单到队列
        $redis = new \Redis();
        $redis->connect('127.0.0.1', '6379');
        do {
            $data = $redis->hGet('orderResult', 'member-'.session('member.member_id'));

        } while($data == 'processing');


        switch ($data) {
            case 'success':
                $this->ajaxReturn(['error'=>0]);
                break;
            case 'quantity error':
            case 'order error':
                $this->ajaxReturn(['error'=>1]);
                break;
        }

    }
}