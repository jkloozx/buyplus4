<?php
namespace Home\Model;

// use Think\Model;
// 导入关联模型基础类
use Think\Model\RelationModel;

class MemberModel extends RelationModel
{
    const MODEL_REGISTER = 101;// 自定义的时机的常量

    // 开启批量验证
    public $patchValidate = true;

    // validate 属性 存储了自定义的验证规则
    // 多条规则, 使用数组存储
    protected $_validate = [ 
        // 每个元素, 表示一条规则, 使用数组表示
        // 内置规则
        ['email', 'require', '邮箱必须'],
        ['email', 'email', '邮箱格式不正确'],
        ['email', '', '邮箱不能重复', self::EXISTS_VALIDATE, 'unique', self::MODEL_REGISTER],

        // 附加规则
        ['nickname', 'require', '昵称必须'],
        ['nickname', '4,32', '昵称长度在4-32个字符之间', self::EXISTS_VALIDATE, 'length'],

        // 不能重复
        ['nickname', '', '昵称不能重复', self::EXISTS_VALIDATE, 'unique', self::MODEL_REGISTER],// 只有在插入时新增时, 提供的nickname才应该与所有的数据不重复.
        ['nickname', 'uniqueUpdate', '昵称不能重复', self::EXISTS_VALIDATE, 'callback', self::MODEL_UPDATE],// 除了当前ID外, 不能重复

        ['password', 'require', '密码必须'],
        ['password', '6,24', '密码长度在6到24个字符之间', self::EXISTS_VALIDATE, 'length'],
        ['password', 'checkStrong', '密码至少应该包含:大写字母, 小写字母, 数字, 特殊符号(!@#$%^&*()-+=.) 中的两类', self::EXISTS_VALIDATE, 'callback'],

        ['confirm', 'password', '两次输入密码不一致', self::EXISTS_VALIDATE, 'confirm'],

        ['newsletter', 'require', '请选择是否订阅'],
        ['newsletter', ['0', '1'], '请选择合适的订阅选项', self::EXISTS_VALIDATE, 'in'],

        ['agree', '1', '请同意隐私政策', self::MUST_VALIDATE, 'equal', self::MODEL_REGISTER],

    ];

    public function uniqueUpdate($value)
    {
        // 获取当前的ID, 检测除了当前ID外是否重复.
        
    }
    /**
     * 自定义的检测密码强度的规则
     * @param  [type] $value 输入的值
     * @return bool        验证结果
     */
    public function checkStrong($value)
    {
        $strong = 0;
        if (preg_match('/[a-z]/', $value)) {
            // 包含小写字母
            ++ $strong;
        }
        if (preg_match('/[A-Z]/', $value)) {
            // 包含大写字母
            ++ $strong;
        }
        if (preg_match('/[0-9]/', $value)) {
            // 包含数字
            ++ $strong;
        }
        if (preg_match('/[!@#$%^&*()\-+=.]/', $value)) {
            // 包含特殊符号
            ++ $strong;
        }
        return $strong >= 2;// 两种以上符合强度验证

    }


    public $_auto = [// 自动完成规则属性
        ['register_time', 'time', self::MODEL_REGISTER, 'function'], 
        // 生成每个用户的密码盐值, makeSalt
        ['password_salt', 'makeSalt', self::MODEL_BOTH, 'callback'],
        // 自定义加盐md5
        ['password', 'saltMd5', self::MODEL_BOTH, 'callback'], 
        // ['password', 'md5', self::MODEL_BOTH, 'function'], 

    ];
    public function saltMd5($value)
    {
        // 加盐之后的密码md5
        return md5($value . $this->password_salt);
    }
    /**
     * 自定义完成规则
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function makeSalt($value)
    {
        // 返回随机字符串即可
        $length = mt_rand(2, 8);
        // 时间戳的md5之, 取得前length个字符, 作为盐值
        return $this->password_salt = substr(md5(time()), 0, $length);
    }

    
    /**
     * 会员上的复杂逻辑方法
     * @return mixed 成功返回会员信息数组, 失败返回false
     */
    public function checkMember($email, $password)
    {   
        // 利用字段email查询
        $member = $this->getByEmail($email);
        // 判断是否检索到
        if (! $member) {
            // 没有查询到, email匹配失败
            return false;
        }

        // 判断密码
        if ($member['password'] == md5($password . $member['password_salt'])) {
            // 会员合法
            return $member;
        } else {
            // 密码错误
            return false;
        }

    }

}