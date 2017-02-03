<?php

namespace Home\Model;


use Think\Model;

class CategoryModel extends Model
{

    public function getNestedList()
    {
        // 获取全部的可用分类
        $list = $this->where(['category_id'=>['neq', '1'], 'is_used'=>1, 'is_nav'=>1])->order('sort_number')->select();

        // 递归整理成嵌套结构
        $nestedList = $this->getNested($list);

        return $nestedList;
    }

    /**
     * 获取嵌套列表
     * @param $list
     */
    protected function getNested($list, $category_id=0)
    {
        $children = [];// 存储当前$category_id分类下所有的自分类
        foreach($list as $row) {
            if ($row['parent_id'] == $category_id) {
                // 获取当前子分类的后代分类
                $row['children'] = $this->getNested($list, $row['category_id']);
                // 将当前分类加入到children数组
                $children[] = $row;
            }

        }

        return $children;
    }

}