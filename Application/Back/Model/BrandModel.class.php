<?php
namespace Back\Model;

use Think\Model;

class BrandModel extends Model
{
    // 验证规则
    protected $patchValidate = true;
    protected $_validate = [
        ['title', 'require', '品牌不能为空'],
        ['title', '', '品牌已经存在', self::EXISTS_VALIDATE, 'unique'],

        ['site', 'url', '请填写正确的URL地址', self::VALUE_VALIDATE],

        ['sort_number', 'number', '排序需要整数'],
    ];

    // 完成规则
    protected $_auto = [
        ['created_at', 'time', self::MODEL_INSERT, 'function'],
        ['updated_at', 'time', self::MODEL_BOTH, 'function'],
    ];
}