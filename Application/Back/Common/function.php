<?php


/**
 * 生成后台字段链接的函数
 */
function UField($route, $sort, $field, $filter=[])
{
    $params = [];

    // 是当前字段
    $params['sort_field'] = $field;
    // 不是当前的排序字段, 生成当前字段的升序排序URL即可
    if ($sort['field'] != $field) {
        $params['sort_type'] = 'asc';
    } else {
        // 判断新的判续方式, 原来是asc则生成desc
        if (strtolower($sort['type']) == 'asc') {
            $params['sort_type'] = 'desc';
        } else {
            $params['sort_type'] = 'asc';
        }
    }
    // 使用U函数生成链接地址, 将查询参数与排序参数和并在一起
    return U($route, array_merge($filter, $params));
}

/**
 * 生成排序字段的 升降类
 * @param $sort
 * @param $field
 */
function CField($sort, $field)
{
    if($sort['field'] == $field) {
        return $sort['type'];
    } else {
        return '';
    }
}