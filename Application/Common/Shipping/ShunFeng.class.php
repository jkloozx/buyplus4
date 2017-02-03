<?php

namespace Common\Shipping;
use Common\Interfaces\I_Shipping;

class ShunFeng implements I_Shipping
{

    public function key()
    {
        return 'ShunFeng';
    }

    public function title()
    {
        return '顺丰快递';
    }

    public function price()
    {
        // 距离, 重量, 尺寸, 计算价格.(快递公司确定)
        return 10.0;
    }

}