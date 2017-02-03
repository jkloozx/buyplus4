<?php


namespace Home\Model;


use Think\Model\RelationModel;

class GoodsModel extends RelationModel
{

    protected $_link = [
        'galleryList' => [
            'mapping_type'  => self::HAS_MANY,
            'class_name'    => 'Gallery',
            'foreign_key'   => 'goods_id',
        ]
    ];

}