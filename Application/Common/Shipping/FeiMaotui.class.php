<?php


namespace Common\Shipping;


use Common\Interfaces\I_Shipping;

class FeiMaotui implements I_Shipping
{
    public function key()
    {
        return 'FeiMaotui';
    }

    public function title()
    {
        return '飞毛腿';
    }

    public function price()
    {
        // 距离, 重量, 尺寸, 计算价格.(快递公司确定)
        return 12.0;
    }

}