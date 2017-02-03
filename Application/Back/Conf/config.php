<?php

return [
 'USER_AUTH_ON' => true, // 是否需要认证
 'USER_AUTH_TYPE' => 2, // 登录认证 认证类型
 'USER_AUTH_KEY' => 'user_id', // 认证识别号
// REQUIRE_AUTH_MODULE  需要认证模块'
 'NOT_AUTH_MODULE' => 'Manage', // 无需认证模块
 'NOT_AUTH_ACTION' => 'index', // 无需验证的动作
 'USER_AUTH_GATEWAY' => '/Back/Admin/login', // 认证网关
 'ADMIN_AUTH_KEY' => 'superAdmin',  //标识是否为超级管理员的下标,
// RBAC_DB_DSN  数据库连接DSN
 'RBAC_ROLE_TABLE' => 'role', // 角色表名称
 'RBAC_USER_TABLE' => 'role_user', // 角色用户关系表名称
 'RBAC_ACCESS_TABLE' => 'access', // 权限表名称
 'RBAC_NODE_TABLE' => 'node', // 节点表名称
];