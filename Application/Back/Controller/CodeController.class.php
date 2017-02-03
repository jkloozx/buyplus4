<?php

namespace Back\Controller;

use Think\Controller;

class CodeController extends Controller
{

    /**
     * 生成CRUD动作, table 和 field两个步骤, 分开进行处理
     */
    public function crudAction()
    {
        // 确定当前步骤, 没有传递, 当前进入第一步.
        $step = I('get.step', 'table');

        if ('table' == $step) {
            if (IS_POST) {
                // 检测表是否存在
                // 记录当前表名到session中,
                session('crud', ['table'=>I('post.table'), 'title'=>I('post.title')]);
                // 进入到步骤二
                $this->redirect('crud', ['step'=>'field']);

            } else {
                $this->assign('step', 'table');// 分配步骤一
                $this->display();
            }
        }

        elseif ('field' == $step) {
            // 步骤二
            // 先获取模型
            $model = M(session('crud.table'));//goods Goods, member_login_log MemberLoginLog
            // 获取表的字段列表, 利用模型即可完成
            $fieldList = $model->getDbFields();

            if (IS_POST) {
                // 生成代码
                // 〇: 生成需要替换的数据
                // 1, 模型和控制器 名
                // 表名 使用_拆分后,首字母大写, 再连接
                // explode, 拆分一个字符串为数组
                // array_map, 针对数组元素, 调用特定的函数, 返回每个元素执行函数后的结果数组
                // ucfirst, 首字母大写
                // implode, 数组元素连接成一个字符串
                $controllerName = $modelName = implode(array_map('ucfirst', explode('_', $model->getModelName())));
                // 2, 主键字段名(存在一个独立的主键字段)
                $pkField = $model->getPk();
                // 3, 获取当前的字段列表
                $fieldCollection = I('post.field'); // 包含配置信息的全部字段
                // 4, 获取表对应的展示标题
                $tableTitle = session('crud.title');






                // 一: 生成控制器代码
                // 利用控制器模板, 完成控制器代码生成
                $template = APP_PATH . 'Back/Code/controller.template';
                $content = file_get_contents($template);
                // 记录需要替换的对应内容
                $search = ['__CONTROLLER__', '__MODEL__', '__PK_FIELD__'];
                $replace = [$controllerName, $modelName, $pkField];
                $content = str_replace($search, $replace, $content);
                $file = APP_PATH . 'Back/Controller/' . $controllerName . 'Controller.class.php';
                file_put_contents($file, $content);
                echo '控制器文件: ', $file, ' 生成成功', "\n<br>";

                // 二: 生成模型代码
                $template = APP_PATH . 'Back/Code/model.template';
                $content = file_get_contents($template);
                // 记录需要替换的对应内容
                $search = ['__CONTROLLER__', '__MODEL__', '__PK_FIELD__'];
                $replace = [$controllerName, $modelName, $pkField];
                $content = str_replace($search, $replace, $content);
                $file = APP_PATH . 'Back/Model/' . $modelName . 'Model.class.php';
                file_put_contents($file, $content);
                echo '模型文件: ', $file, ' 生成成功', "\n<br>";


                // 三: 生成模板
                // 1, 生成字段列表模板
                $theadTdList = $tbodyTdList = $setFieldList = '';
                foreach($fieldCollection as $field => $option) {
                    // 形成字段相关的查找替换
                    $search = ['__FIELD__', '__FIELD_TITLE__',];
                    $replace = [$field, $option['title']!=='' ? $option['title'] : $field];

                    // 1, 为list模板生成: 字段和选项
                    if (isset($option['is_list'])) {// checkbox选中则存在, 未选中则不存在
                        // 需要在列表中显示
                        // 处理thead, 判断是否做排序
                        if (isset($option['is_sort'])) {
                            // 需排序
                            $template = APP_PATH . 'Back/Code/listTheadTdSortView.template';
                        } else {
                            // 不需要排序
                            $template = APP_PATH . 'Back/Code/listTheadTdView.template';
                        }
                        $content = file_get_contents($template);
                        $content = str_replace($search, $replace, $content);
                        $theadTdList .= $content;

                        // 处理tbody部分
                        $template = APP_PATH . 'Back/Code/listTbodyTdView.template';
                        $content = file_get_contents($template);
                        $content = str_replace($search, $replace, $content);
                        $tbodyTdList .= $content;

                    }

                    // 2, 为set模板,生成字段
                    if (isset($option['is_set'])) {
                        // 需要被设置(主键字段除外)
                        if ($field == $pkField) continue;

                        $template = APP_PATH . 'Back/Code/setFieldView.template';
                        $content = file_get_contents($template);
                        $content = str_replace($search, $replace, $content);
                        $setFieldList .= $content;
                    }
                }

                // 替换list模板整体
                $template = APP_PATH . 'Back/Code/listView.template';
                $content = file_get_contents($template);
                // 记录需要替换的对应内容
                $search = ['__TITLE__', '__THEAD_LIST__', '__TBODY_LIST__', '__PK_FIELD__'];
                $replace = [$tableTitle, $theadTdList, $tbodyTdList, $pkField];
                $content = str_replace($search, $replace, $content);
                $path = APP_PATH . 'Back/View/' . $controllerName;
                if (!is_dir($path)) {
                    mkdir ($path);
                }
                $file = $path . '/list.html';
                file_put_contents($file, $content);
                echo '列表视图文件: ', $file, ' 生成成功', "\n<br>";


                // 替换set整体模板
                $template = APP_PATH . 'Back/Code/setView.template';
                $content = file_get_contents($template);
                // 记录需要替换的对应内容
                $search = ['__TITLE__', '__FIELD_LIST__', '__PK_FIELD__'];
                $replace = [$tableTitle, $setFieldList, $pkField];
                $content = str_replace($search, $replace, $content);
                $path = APP_PATH . 'Back/View/' . $controllerName;
                if (!is_dir($path)) {
                    mkdir ($path);
                }
                $file = $path . '/set.html';
                file_put_contents($file, $content);
                echo 'set视图文件: ', $file, ' 生成成功', "\n<br>";

            } else {

                $this->assign('fieldList', $fieldList);
                $this->assign('step', 'field');
                $this->display();
            }
        }
    }
}