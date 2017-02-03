<?php

namespace Back\Controller;


use Think\Controller;
use Org\Util\Rbac;
class CommonController extends Controller
{
    public function _initialize()
    {
        C('RBAC_ROLE_TABLE', C('DB_PREFIX') . C('RBAC_ROLE_TABLE')); // 角色表名称
        C('RBAC_USER_TABLE', C('DB_PREFIX') . C('RBAC_USER_TABLE'));
        C('RBAC_ACCESS_TABLE', C('DB_PREFIX') . C('RBAC_ACCESS_TABLE'));
        C('RBAC_NODE_TABLE', C('DB_PREFIX') . C('RBAC_NODE_TABLE'));

        // 校验登录
        Rbac::checkLogin();

        // 校验权限
        if( ! Rbac::AccessDecision()) {
            // 没有权限, 跳转到登录页
            $this->error('没有权限', U('Admin/login'));
        }
//        dump(Rbac::getAccessList(session(C('USER_AUTH_KEY'))));
        $this->assign('accessList', Rbac::getAccessList(session(C('USER_AUTH_KEY'))));
    }



}