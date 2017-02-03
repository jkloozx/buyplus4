<?php


namespace Back\Model;


use Think\Model\RelationModel;

class SettingModel extends RelationModel
{

    // 定义关联
    protected $_link = [
        // 一个配置项可能拥有多个选项预设值
        'optionList' => [
            'mapping_type'  => self::HAS_MANY,
            'class_name'    => 'SettingOption',
            'foreign_key'   => 'setting_id',
        ],
    ];

}