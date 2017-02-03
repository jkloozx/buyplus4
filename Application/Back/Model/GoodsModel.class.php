<?php
namespace Back\Model;

use Think\Model;

class GoodsModel extends Model
{
    // 验证规则
    protected $patchValidate = true;
    protected $_validate = [
        ['sku_id', 'chkSku', '请选择合理的库存单位', self::EXISTS_VALIDATE, 'callback', self::MODEL_BOTH],
        ['tax_id', 'chkTax', '请选择合理的税类型', self::EXISTS_VALIDATE, 'callback', self::MODEL_BOTH],
//        ['length_unit_id', 'chkLengthUnit', self::MODEL_BOTH, 'callback'],
//        ['weight_unit_id', 'chkWeightUnit', self::MODEL_BOTH, 'callback'],
//        ['stock_status_id', 'chkStockStatus', self::MODEL_BOTH, 'callback'],
//        ['brand_id', 'chkBrand', self::MODEL_BOTH, 'callback'],
//        ['category_id', 'chkCategory', self::MODEL_BOTH, 'callback'],
    ];

    // 完成规则
    protected $_auto = [
        ['upc', 'mkUpc', self::MODEL_INSERT, 'callback'],
        ['created_at', 'time', self::MODEL_INSERT, 'function'],
        ['updated_at', 'time', self::MODEL_BOTH, 'function'],
        ['date_available', 'mkDateAvailable', self::MODEL_BOTH, 'callback']
    ];

    protected function mkDateaVailable($value) {
//        if ($value !== '') {
//            return $value;
//        }
        return date('Y-m-d');
    }

    protected function mkUpc($value)
    {
        // 如果用户指定, 使用用户的
        if ($value !== '') {
            return $value;
        }
        // 否则使用自动生成
        return time() . mt_rand(100, 999) . mt_rand(100, 999) . mt_rand(100, 999);// 伪随机数
    }

    protected function chkSku($value)
    {
        return (bool) M('Sku')->find($value);
    }
    protected function chkTax($value)
    {
        return (bool) M('Tax')->find($value);
    }
}