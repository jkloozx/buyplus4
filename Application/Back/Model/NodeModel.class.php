<?php
namespace Back\Model;

use Think\Model;

class NodeModel extends Model
{
    // 验证规则
    protected $patchValidate = true;
    protected $_validate = [];

    // 完成规则
    protected $_auto = [];


    public function getParentTree()
    {
        // 获取全部的模块和控制器
        $list = $this->where(['level' => ['in', [1, 2]]])->select();
        return $this->tree($list);
    }

    protected  function tree($list, $node_id=0, $deep=0)
    {
        static $tree = [];
        foreach($list as $row) {
            if ($row['pid'] == $node_id) {
                $row['deep'] = $deep;
                $tree[] = $row;
                $this->tree($list, $row['id'], $deep+1);
            }
        }

        return $tree;
    }


    public function getNested()
    {
        $list = $this->select();
        return $this->nested($list);
    }

    protected  function nested($list, $node_id=0)
    {
        $child = [];
        foreach($list as $row) {
            if ($row['pid'] == $node_id) {

                $row['child'] = $this->nested($list, $row['id']);
                $child[] = $row;
            }
        }

        return $child;
    }
}