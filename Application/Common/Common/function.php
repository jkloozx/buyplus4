<?php

/**
 * 用户定义的获取配置的函数
 *
 */
function CC($key, $value=null)
{
    // 在第二个参数不为null时
    if (! is_null($value)) {
        return $GLOBALS['config'][$key] = $value;
    }

    // 获取
    if (isset($GLOBALS['config'][$key])) {
        return $GLOBALS['config'][$key];
    } else {
        // 从setting表中查找
        $model = M('Setting');
        return $model->where(['key'=>$key])->getField('value');
    }
}