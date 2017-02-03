<?php


namespace Back\Controller;

use Think\Controller;
use Think\Page;

class ShippingController extends Controller
{

    public function addAction()
    {
        if (IS_POST) {

            $model = D('Shipping');
            if ($model->create()) {// 校验
                $model->add();// 添加
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

            $this->display('set');
        }
    }

    /**
     * 更新
     */
    public function editAction()
    {

        $model = D('Shipping');
        if (IS_POST) {
            if ($model->create()) {// 校验
                $model->save();// 更新
                $this->redirect('list');// 重定向到列表动作
            } else {
                // 将错误信息存储到session中, 便于下个页面输出错误消息
                session('message', ['error'=>1, 'errorInfo'=>$model->getError()]);
                session('data', $_POST);
                $this->redirect('edit', ['shipping_id'=>I('post.shipping_id')]); // 重定向到添加
           }
       } else {
           $this->assign('message', session('message'));
           session('message', null);// 删除该信息
           // 获取当前编辑的内容, 如果是编辑错误,则显示错误的内容, 如果是没有错误, 则显示原始数据内容
           $this->assign('data', is_null(session('data')) ? $model->find(I('get.shipping_id')) : session('data'));
           session('data', null);
           // 展示
           $this->display('set');
       }
    }

    public function listAction()
    {

        $model = M('Shipping');



        // : 考虑排序
        $sort = [
            'field' => I('get.sort_field', 'sort_number'),
            'type' => I('get.sort_type', 'asc'),
        ];// 默认的排序方式
//        确定排序字符串
        if (! is_null($sort['field'])) {// 没有排序字段
            $sortString = $sort['field'] . ' ' . $sort['type'];
            $model->order($sortString);
        }
//        将当前的排序方式, 分配到模板中
        $this->assign('sort', $sort);


        // 二: 执行查询
        $rows = $model->select();
        foreach($rows as $k=>$row) {
            // 判断对应的文件是否存在
            if (! file_exists(APP_PATH . 'Common/Shipping/' . $row['key'] . '.class.php')) {
                // 已经被删除了
//                从数据表中也删除
                M('Shipping')->where(['key'=>$row['key']])->delete();
            } else {

                $list[$row['key']] = $row;
            }
        }
        // 从数据表获取的内容
        // 三: 从插件目录获取新的内容
        $handle = opendir(APP_PATH . 'Common/Shipping');
        while(false !== $filename = readDir($handle)) {
            if ($filename == '.' || $filename == '..') continue;
            // 从文件名截取类名
            $key = strchr($filename, '.', true);
            // 出现了一个目录中的插件名字
            // 判断 该方式, 是否在数据表中可以获取到.
            if (! isset($list[$key])) {
                // 出现了没有在数据表中的文件类
                $shippingName = 'Common\Shipping\\' . $key;
                // 判断该类是否是合理的配送类, (通过反射来实现)
                $rc = new \ReflectionClass($shippingName);
                // 结构分析
                if ($rc->implementsInterface('Common\Interfaces\I_Shipping')) {
                    // 是
//                    $shipping = new $shippingName;
                    $shipping = $rc->newInstance();// 代理执行实例化
                    $list[$key] = ['key'=>$key, 'title'=>$shipping->title(), 'is_new'=>1];
                }
            }
        }

        closedir($handle);
        $this->assign('list', $list);

        $this->display();
    }

    public function installAction()
    {
        $key = I('get.key');
        $shippingName = 'Common\Shipping\\' . $key;
        $shipping = new $shippingName;
        $data = [
            'key' => $key,
            'title' => $shipping->title(),
        ];
        M('Shipping')->add($data);

        $this->redirect('list');
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
                $model = M('Shipping');
                $model->where(['shipping_id'=>['in', I('post.selected')]])->delete();
                break;
        }
        $this->redirect('list');
    }
}