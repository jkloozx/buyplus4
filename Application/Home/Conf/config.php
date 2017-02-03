<?php
return array(
	//'配置项'=>'配置值'
    'DEFAULT_CONTROLLER'    => 'Shop',
    'DEFAULT_ACTION'    => 'index',

    'TMPL_ACTION_ERROR'     =>  'Common/error', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   =>  'Common/success', // 默认成功跳转对应的模板文件
    //
    'URL_ROUTER_ON' => true, // 开启自定义路由
    'URL_ROUTE_RULES'   => [ // 自定义路由规则

        'register'  => 'Member/register', // 路由到用户的注册动作
        'login'     => 'Member/login',// 登录
        'logout'     => 'Member/logout',// 注销登录
        'center'    => 'Member/center',

        'addressList'   => 'Member/addressList',// 获取会员的收货地址
        'childRegion' => 'Member/childRegion',// 获取子地区
        'addAddress'    => 'Member/addAddress',


        'category/nestedList'   => ['Shop/category', ['operate'=>'nestedList']],
        'goods/new' => ['Shop/goods', ['operate'=>'new']],
        'goods/special' => ['Shop/goods', ['operate'=>'special']],
        'goods/promote' => ['Shop/goods', ['operate'=>'promote']],
        'goods/show' => ['Shop/goods', ['operate'=>'show']],

        'show/:goods_id'   => 'Shop/show',
        'breadcrumb'   => 'Shop/breadcrumb',
        'addToCart'     => 'Buy/addToCart',
        'removeFromCart'     => 'Buy/removeFromCart',
        'updateQuantity'     => 'Buy/updateQuantity',
        'cartInfo'      => 'Buy/cartInfo',
        'cart'      => 'Buy/cart',
        'checkout'  => 'Buy/checkout',
        'shippingList'  => 'Buy/shippingList',
        'order' => 'Buy/order',
        'result'    => 'Buy/result',
        'orderState'    => 'Buy/orderState',

    ],
    'URL_MODEL' => 2, // URL模式

    'TMPL_PARSE_STRING' => [
        
    ],
);