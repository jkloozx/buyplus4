<?php

namespace Back\Controller;

use Think\Controller;


class MemberController extends Controller
{

    public function listAction()
    {

        $this->assign('list', M('Member')->select());

        $this->display();
    }



    public function multiAction()
    {
        // 获取操作类型
        $type = I('post.type', null);
        if (is_null($type)) {
            $this->redirect('list');
        }

        // 根据操作类型进行不同的操作
        switch ($type) {

            case 'email': 
                // 不做: 找到每个需要发送的邮件的emial地址, 对其发送邮件即可
                
                // 将任务加入队列
                
                $redis = new \Redis;
                $redis->connect('192.168.118.128', '6379');
                // 将需要发送邮件的会员的ID加入队列, 任务入队列
                foreach(I('post.selected', []) as $member_id) {
                    $redis->lpush('memberEmailQueue', $member_id); 
                }
                $redis->close();
                $this->success('邮件队列加入成功, 需要耐心等待发送完成通知', U('list'), 1); 
                
            break;
        }
    }
}