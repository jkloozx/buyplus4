<?php


namespace Back\Controller;

use Think\Controller;
use Think\Page;

class CategoryController extends Controller
{

    public function addAction()
    {
        if (IS_POST) {

            $model = D('Category');
            if ($model->create()) {// 校验
                $model->add();// 添加
                // 初始缓存配置
                S([
                    'type' => 'Memcache',
                    'host'  => '192.168.118.1',
                    'port'  => '11211',
                ]);
                // 删除
                S('category_tree', null);
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

            // 分类列表
            $modelCategory = D('Category');
            $this->assign('list', $modelCategory->getTreeList());

            $this->display('set');
        }
    }

    /**
     * 更新
     */
    public function editAction()
    {

        $model = D('Category');
        if (IS_POST) {
            if ($model->create()) {// 校验

                $model->save();// 更新
                // 初始缓存配置
                S([
                    'type' => 'Memcache',
                    'host'  => '192.168.118.1',// CC('MEMCACHE_HOST')
                    'port'  => '11211',
                ]);
                // 删除
                S('category_tree', null);
                $this->redirect('list');// 重定向到列表动作
            } else {

                // 将错误信息存储到session中, 便于下个页面输出错误消息
                session('message', ['error'=>1, 'errorInfo'=>$model->getError()]);
                session('data', $_POST);
                $this->redirect('edit', ['category_id'=>I('post.category_id')]); // 重定向到添加
           }
       } else {
           $this->assign('message', session('message'));
           session('message', null);// 删除该信息
           // 获取当前编辑的内容, 如果是编辑错误,则显示错误的内容, 如果是没有错误, 则显示原始数据内容
           $this->assign('data', is_null(session('data')) ? $model->find(I('get.category_id')) : session('data'));
           session('data', null);

            // 分类列表
           $modelCategory = D('Category');
           $this->assign('list', $modelCategory->getTreeList());
           // 展示
           $this->display('set');
       }
    }

    public function listAction()
    {

        $modelCategory = D('Category');

        $this->assign('list', $modelCategory->getTreeList());

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
                $model = M('Category');
                $model->where(['category_id'=>['in', I('post.selected')]])->delete();

                // 初始缓存配置
                S([
                    'type' => 'Memcache',
                    'host'  => '192.168.118.128',
                    'port'  => '11211',
                ]);
                // 删除
                S('category_tree', null);
                break;
        }
        $this->redirect('list');
    }
}