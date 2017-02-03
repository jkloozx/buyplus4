<?php
namespace Back\Model;


use Think\Model\RelationModel;

class AttributeModel extends RelationModel
{
    // 验证规则
    protected $patchValidate = true;
    protected $_validate = [];

    // 完成规则
    protected $_auto = [];

    // 关联属性
    protected $_link = [
        'optionList' => [
            'mapping_type' => self::HAS_MANY,
            'class_name'    => 'AttributeOption',
            'foreign_key'   => 'attribute_id',
        ],
    ];
}