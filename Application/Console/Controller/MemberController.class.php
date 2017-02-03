<?php


namespace Console\Controller;

use Think\Controller;

class MemberController extends Controller
{


    public function emailAction()
    {
        $redis = new \Redis();
        $redis->connect('192.168.118.128', '6379');
        $m_member = M('Member');
        // 守护执行
        while (true) {
            // 检测队列, 是否有新任
            // 没有新任务, 继续检测
            if ( ! $member_id = $redis->rpop('memberEmailQueue') ) {
                continue;
            }
            // 有会员, 需要发送邮件
            // 获取会员信息(emial地址)
            $m_member->field('nickname, email')->find($member_id);

            // 发送邮件!
            $this->sendMail($m_member->email, 'HelloKang@hellokang.net', '圣诞白金会员回馈', "你好, $m_member->nickname:\n 圣诞快乐!\n此致敬礼!");

        }
    }

    public function sendMail($to, $from, $subject, $body)
    {
        sleep(3);
        echo 'From:', $from, ' To:', $to, ' Subject: ', iconv('utf-8', 'gbk', $subject), ' Body:', iconv('utf-8', 'gbk', $body) , "\n", '------------------------------', "\n";
    }
}