<?php


namespace Back\Controller;

use Org\Util\Rbac;
use Think\Controller;
use Think\Page;

class AdminController extends Controller
{

    public function loginAction()
    {

        if(IS_POST) {
            // 拿到用户名和密码校验即可
            // 使用username查找用户
            $modelAdmin = M('Admin');
            $admin = $modelAdmin
                ->where([
                    'username'=>I('post.username', '', 'trim')
                ])
                ->find();
            if (!$admin || md5(I('post.password').$admin['password_salt']) != $admin['password']) {
                // 没有找到 或者 密码错误, 设置错误信息
                session('message', ['error'=>1, 'errorInfo'=>'用户名或密码错误']);
                session('data', I('post.'));
                // 重定向到登录
                $this->redirect('login');
            }

            // 校验通过
            unset($admin['password']);
            unset($admin['password_salt']);
            session('admin', $admin);

            // 为RBAC, 设置认证识别号
            session(C('USER_AUTH_KEY'), $admin['user_id']);
            // 判断是否为超级管理员
            if ($admin['username'] == 'helloKang') {
                session(C('ADMIN_AUTH_KEY'), true);
            }

            // 为RBAC, 存档当前权限
            Rbac::saveAccessList(); // use Org\Util\Rbac;
            $this->redirect('Manage/index');

        } else {

            $this->assign('message', session('message'));
            session('message', null);
            $this->display();
        }
    }


    public function logoutAction()
    {
        session('admin', null);

        // RABC
        session(C('USER_AUTH_KEY'), null);
        session(C('ADMIN_AUTH_KEY'), null);
        session('_ACCESS_LIST', null);


        $this->redirect('login');
    }

    public function addAction()
    {
        if (IS_POST) {

            $model = D('Admin');
            if ($model->create()) {// 校验
                $user_id = $model->add();// 添加

                $rows = array_map(function($role_id) use ($user_id) {
                    return [
                        'role_id'   => $role_id,
                        'user_id'   => $user_id,
                    ];
                }, I('post.roleList', []));
                M('RoleUser')->addAll($rows);

                $this->redirect('list');// 重定向到列表动作
            } else {
                // 将错误信息存储到session中, 便于下个页面输出错误消息
                session('message', ['error'=>1, 'errorInfo'=>$model->getError()]);
                session('data', $_POST);
                $this->redirect('add'); // 重定向到添加
            }
        } else {
//            获取到, 分配到模板, 删除, 做一个一次性的session会话数据
            $this->assign('message', session('message'));
            session('message', null);// 删除该信息
            $this->assign('data', session('data'));
            session('data', null);

            // 角色列表
            $this->assign('roleList', M('Role')->select());

            $this->display('set');
        }
    }

    /**
     * 更新
     */
    public function editAction()
    {

        $model = D('Admin');
        if (IS_POST) {
            if ($model->create()) {// 校验
                $model->save();// 更新


                $user_id = I('post.user_id');
                M('RoleUser')->where(['user_id'=>$user_id])->delete();

                $rows = array_map(function($role_id) use ($user_id) {
                    return [
                        'role_id'   => $role_id,
                        'user_id'   => $user_id,
                    ];
                }, I('post.roleList', []));
                M('RoleUser')->addAll($rows);

                $this->redirect('list');// 重定向到列表动作
            } else {
                // 将错误信息存储到session中, 便于下个页面输出错误消息
                session('message', ['error'=>1, 'errorInfo'=>$model->getError()]);
                session('data', $_POST);
                $this->redirect('edit', ['user_id'=>I('post.user_id')]); // 重定向到添加
           }
       } else {
           $this->assign('message', session('message'));
           session('message', null);// 删除该信息
           // 获取当前编辑的内容, 如果是编辑错误,则显示错误的内容, 如果是没有错误, 则显示原始数据内容
            $data = $model->find(I('get.user_id'));
            $data['roleList'] = M('RoleUser')->where(['user_id'=>$data['user_id']])->getField('role_id', true);

           $this->assign('data', is_null(session('data')) ? $data : session('data'));
           session('data', null);

            // 全部角色列表
            $this->assign('roleList', M('Role')->select());
           // 展示
           $this->display('set');
       }
    }

    public function listAction()
    {

        $model = M('Admin');

        // 一: 查询条件
        $cond = [];// 初始化查询条件
        $filter = []; // 初始化一个用于记录查询查询的数组, 分配到视图模板中
        // 自己完成的部分, 特殊的业务逻辑
        // 继续判断其他的字段, 入$cond和$filter数组即可
        // 所有检索结束, 分配搜索条件
        $this->assign('filter', $filter);


        // 二: 考虑分页
        $pagesize = 4;// 每页记录数
        // 计算总页数
        $total = $model->where($cond)->count();// 所有符合条件的记录数
        $totalPage = ceil($total/$pagesize);// 计算总页数
        $p = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';// 当前的翻页参数
        $page = I('get.'.$p, '1', 'intval');
        // 考虑是否越界
        if ($page < 1) {// 小于第一页
            $page = 1;
        }
        if ($page > $totalPage) { // 考虑大于总页数
            $page = $totalPage;
        }
        // 为模型设置分页操作, 页码和每页记录数作为参数
        $model->page("$page, $pagesize");

        // 形成翻页操作接口
        $toolPage = new Page($total, $pagesize);
        // 定制样式结构
        $toolPage->setConfig('header', '显示开始 %FIRST_NUM% 到 %LAST_NUM% 之 %TOTAL_ROW% （总 %TOTAL_PAGE% 页）');
        $toolPage->setConfig('prev', '&lt;');
        $toolPage->setConfig('next', '&gt;');
        $toolPage->setConfig('first', '|&lt;');
        $toolPage->lastSuffix =  false;// 末页使用非页码
        $toolPage->setConfig('last', '&gt;|');
        $toolPage->setConfig('theme', '<div class="col-sm-6 text-left"><ul class="pagination">%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%</ul></div><div class="col-sm-6 text-right">%HEADER%</div>');
        $this->assign('pageHtml', $toolPage->show());


        // 三: 考虑排序
        $sort = [
            'field' => I('get.sort_field', null),
            'type' => I('get.sort_type', 'asc'),
        ];// 默认的排序方式
//        确定排序字符串
        if (! is_null($sort['field'])) {// 没有排序字段
            $sortString = $sort['field'] . ' ' . $sort['type'];
            $model->order($sortString);
        }
//        将当前的排序方式, 分配到模板中
        $this->assign('sort', $sort);


        // 四: 执行查询
        $list = $model->where($cond)->select();
        $this->assign('list', $list);

        $this->display();
    }

    /**
     * 提供批量处理操作
     */
    public function multiAction()
    {

        $operate = I('post.operate', null);


        // 先处理删除
        $operate = 'delete';
        switch ($operate) {
            case 'delete':
                $model = M('Admin');
                $model->where(['user_id'=>['in', I('post.selected')]])->delete();
                break;
        }
        $this->redirect('list');
    }
}