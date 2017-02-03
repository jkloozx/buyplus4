<?php
namespace Back\Model;

use Think\Model;

class ShippingModel extends Model
{
    // 验证规则
    protected $patchValidate = true;
    protected $_validate = [];

    // 完成规则
    protected $_auto = [];
}