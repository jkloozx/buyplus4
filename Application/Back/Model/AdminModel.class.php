<?php
namespace Back\Model;

use Think\Model;

class AdminModel extends Model
{
    // 验证规则
    protected $patchValidate = true;
    protected $_validate = [];

    // 完成规则
    protected $_auto = [
        ['password_salt', 'mkSalt', self::MODEL_BOTH, 'callback'],
        ['password', 'mkPassword', self::MODEL_BOTH, 'callback'],
    ];

    protected function mkSalt($value)
    {

        return $this->salt = mt_rand(1000, 9999);
    }

    protected function mkPassword($value)
    {
        return md5($value . $this->salt);
    }
}