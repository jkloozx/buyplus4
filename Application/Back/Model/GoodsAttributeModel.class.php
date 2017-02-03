<?php


namespace Back\Model;


use Think\Model\RelationModel;

class GoodsAttributeModel extends RelationModel
{

    protected $_link = [
        'goodsOptionList' => [
            'mapping_type'  => self::HAS_MANY,
            'class_name'    => 'GoodsAttributeOption',
            'foreign_key'   => 'goods_attribute_id',
        ],
    ];

}