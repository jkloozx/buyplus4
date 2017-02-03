<?php

namespace Back\Controller;


use Think\Controller;

class SettingController extends Controller
{
    /**
     * 设置页面
     */
    public function setAction()
    {
        S(['type'=>'File']);// 文件缓存

        if (! $groupList = S('groupList')) {
            // 没有缓存
            // 一: 获取所有的配置项分组
            $modelGroup = M('SettingGroup');
            $groupList = $modelGroup->order('sort_number')->select();
            // 生成缓存
            S('groupList', $groupList);
        }
        $this->assign('groupList', $groupList);


        if (! $settingGroupList = S('settingGroupList')) {
            // 二: 获取所有的配置项, 依据分组管理(数据整理成, 可以找到某个分组内的全部配置项的格式)
            // 1, 获取所有的配置项
            $modelSetting = D('Setting');
            $settingList = $modelSetting
                ->alias('s')
                // 当前配置项的类型
                ->join('left join __SETTING_TYPE__ st using(setting_type_id)')
                ->order('sort_number')
                // 关联模型, 当前配置项的选项预设值列表
                ->relation(true)
                ->select();
            // 2, 整理成分组格式, 设计的格式如下
//            [
//                'group_id' => [当前组内的全部配置项]
//            ]
            foreach($groupList as $group) {
                $settingGroupList[$group['setting_group_id']] = [];
            }
            foreach($settingList as $setting) {// 遍历每个配置项
                $settingGroupList[$setting['setting_group_id']][] = $setting;
            }


            // 生成缓存
            S('settingGroupList', $settingGroupList);
        }
        $this->assign('settingGroupList', $settingGroupList);
        $this->display();


    }

    /**
     * 更新配置项
     */
    public function updateAction()
    {
        // 获取所有的配置项的修改值
        $settingList = I('post.setting', []);

        $model = M('Setting');
        // 找到所有的配置项ID
        $allSettingList = $model->getField('setting_id', true);//
        // 遍历所有的配置项
        foreach($allSettingList as $setting_id) {
            // 判断当前setting_id是否出现在从post获取的配置项列表中
            // 存在, 有更新数据, 使用更新数据, 不存在使用''空字符串
            $value = isset($settingList[$setting_id]) ? $settingList[$setting_id] : '';
            // 使用模型完成更新
            $model->save([
                'setting_id'    => $setting_id,
                // 判断是否多选的数组, 如果是数组, 则使用逗号连接
                'value'         => is_array($value) ? implode(',', $value) : $value,
            ]);
        }

        // 删除缓存
        S(['type'=>'File']);// 文件缓存
        S('groupList', null);
        S('settingGroupList', null);

        // 重定向到set页面
        $this->redirect('set');
    }

    public function ajaxAction()
    {

        // 处理 id和值
        $data['setting_id'] = I('request.setting_id');
        $value = I('request.value');
        $data['value'] = is_array($value) ? implode(',', $value) : $value;
        $model = M('Setting');

        // 删除缓存
        S(['type'=>'File']);// 文件缓存
        S('groupList', null);
        S('settingGroupList', null);

        if ($model->save($data)) {
            $this->ajaxReturn(['error'=>0]);
        } else {
            $this->ajaxReturn(['error'=>1, 'errorInfo'=>$model->getError()]);
        }

    }

}