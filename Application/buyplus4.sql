create database buyplus4 charset=utf8;

use buyplus4;
-- 会员表
create table kang_member
(
    member_id int unsigned auto_increment,  -- 主键
    email varchar(255) null default '', -- 邮箱
    telephone varchar(16) null default '', -- 电话
    password char(40) not null default '', -- 密码
    password_salt char(8) not null default '', -- 密码盐值
    nickname varchar(32) not null default '', -- 显示昵称
    gender enum('Male', 'Female', 'Secret') not null default 'Secret',
    newsletter tinyint not null default 0, -- 是否使用email订阅
    register_time int not null default 0, -- 注册时间
    primary key (member_id),
    index (email),
    index (telephone)
) charset=utf8;

-- 会员登录日志
create table kang_member_login_log
(
    member_login_log_id int unsigned auto_increment,
    member_id int unsigned not null default 0, -- 关联字段
    login_time int not null default 0, -- 时间
    login_ip int unsigned not null default 0, -- IP
    login_ua varchar(255) not null default '', -- user-agent
    primary key (member_login_log_id),
    index (member_id)
) charset=utf8;


-- 活动表
create table kang_event
(
    event_id int unsigned auto_increment,
    title varchar(128) not null default '',
    -- begin_time
    -- end_time
    primary key (event_id)
) charset=utf8;

-- 会员活动关联
create table kang_member_event
(
    member_event_id int unsigned auto_increment,
    member_id int unsigned not null default 0,
    event_id int unsigned not null default 0,
    primary key (member_event_id)
) charset=utf8;


-- 商品分类表
drop table if exists kang_category;
create table kang_category (
    category_id int unsigned auto_increment,
    title varchar(32) not null default '',
    parent_id int unsigned not null default 0,
    sort_number int not null default 0,

    image varchar(255) not null default '', -- 分类图片
    image_thumb varchar(255) not null default '', -- 分类图片缩略图
    -- 前台展示
    is_used boolean not null default 1, -- tinyint(1)
    is_nav tinyint not null default 1, -- 针对顶级分类
    -- SEO优化
    meta_title varchar(255) not null default '',
    meta_keywords varchar(255) not null default '',
    meta_description varchar(1024) not null default '',
    primary key (category_id),
    index (parent_id),
    index (sort_number)
) charset=utf8;

insert into kang_category values (1, '未分类', 0, -1, '', '', 0, 0, '', '', '');
insert into kang_category values (5, '眼镜', 0, 0, '', '', 1, 1, '', '', '');
insert into kang_category values (6, '男士眼镜', 5, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (7, '女士眼镜', 5, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (8, '飞行员眼镜', 5, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (9, '驾驶镜', 5, 0,'', '',  1, 0, '', '', '');
insert into kang_category values (10, '太阳镜', 5, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (11, '图书', 0, 0, '', '', 1, 1, '', '', '');
insert into kang_category values (12, '历史', 11, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (14, '科技', 11, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (15, '计算机', 11, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (16, '电子书', 11, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (17, '科普', 14, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (18, '建筑', 14, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (19, '工业技术', 14, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (20, '电子通信', 14, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (21, '自然科学', 14, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (22, '互联网', 15, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (23, '计算机编程', 15, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (24, '硬件，攒机', 15, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (25, '大数据', 15, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (26, '移动开发', 15, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (27, 'PHP', 15, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (28, '近代史', 12, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (29, '当代史', 12, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (30, '古代史', 12, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (31, '先秦百家', 12, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (32, '三皇五帝', 12, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (33, '励志', 16, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (34, '小说', 16, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (35, '成功学', 16, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (36, '经济金融', 16, 0, '', '', 1, 0, '', '', '');
insert into kang_category values (37, '免费', 16, 0, '', '', 1, 0, '', '', '');


-- brand 品牌表
create table kang_brand
(
  brand_id int UNSIGNED AUTO_INCREMENT,
  title varchar(32) not null default '',
  logo varchar(255) not null default '',
  site varchar(255) not null default '', -- 官网地址
  sort_number int not null default 0,
  created_at int not null default 0, -- 创建时间
  updated_at int not null default 0, -- 更新时间
  primary key (brand_id),
  -- 添加一个约束, 使用constraint
  CONSTRAINT `onlyOneTitle` unique key `oneTitle` (title),
  index (sort_number)
) CHARSET=utf8;

-- 配置项
create table kang_setting (
  setting_id int unsigned not null auto_increment,
  `key` varchar(32) not null default '', -- 程序使用的key
  value varchar(255) not null default '', -- 配置项的值
  title varchar(32) not null default '', -- 配置项的标题描述
  setting_type_id int unsigned not null default 0, -- 配置项输入类型ID
  setting_group_id int unsigned not null default 0, -- 配置项分组的ID
  -- 如果是select或者checkbox，提供的选项列表:0-是,1-否
  sort_number int not null default 0, -- 排序标识
  primary key (setting_id),
  index `type` (setting_type_id),
  index `group` (setting_group_id),
  index `order` (sort_number)
) charset=utf8;

-- 配置项分组（不提供管理接口）
create table kang_setting_group (
  setting_group_id int unsigned auto_increment,
  group_title varchar(32) not null default '',-- 分组的标题
  sort_number int not NULL DEFAULT 0,
  primary key (setting_group_id),
  index (sort_number)
) charset=utf8;

-- 配置项类型（不提供管理接口）
create table kang_setting_type (
  setting_type_id int unsigned auto_increment,
  type_title varchar(32) not null default '', -- 类型说明
  primary key (setting_type_id)
) charset=utf8;

-- 配置系统选项预设值
create table kang_setting_option (
  setting_option_id int unsigned auto_increment,-- 选项预设值的option value="option_id"
  option_title varchar(32) not null default '', -- 选项预设值显示内容<option>option_title</option>
  setting_id int unsigned not null default 0,-- 对应的选项ID, 如果选项为单向或多选类型, 则存在对于的选项预设值列表
  primary key (setting_option_id),
  index (setting_id)
) charset=utf8;

-- 加入测试数据
insert into kang_setting_group values (1, '商店设置', 0);--
insert into kang_setting_group values (2, '安全配置', 0);--
insert into kang_setting_group values (3, '展示配置', 0);--

-- 加入测试数据
insert into kang_setting_type values (1, 'text');-- 文本
insert into kang_setting_type values (2, 'textarea');-- 大文本
insert into kang_setting_type values (3, 'select');-- 单选
insert into kang_setting_type values (4, 'select-multi');-- 多选

-- 选项测试数据
insert into kang_setting_option values (1, '是', 2);
insert into kang_setting_option values (2, '否', 2);
insert into kang_setting_option values (3, '注册', 3);
insert into kang_setting_option values (4, '登录', 3);
insert into kang_setting_option values (5, '评论', 3);
insert into kang_setting_option values (6, '生成订单', 3);

-- 配置项测试数据
insert into kang_setting values (1, 'shop_title', 'BuyPlus(败家Shopping)', '商店名称', 1, 1,  0);
insert into kang_setting values (2, 'allow_comment', '1', '是否允许商品评论', 3, 1, 0);
insert into kang_setting values (3, 'use_captcha', '4,5', '哪些页面使用验证码', 4, 2, 0);
insert into kang_setting values (4, 'mate_description', 'BuyPlus(败家Shopping), 用BuyPlus，不败家！', 'mate描述description', 2, 1, 0);
insert into kang_setting values (null, 'goods_new_number', '4', '新商品展示数量', 1, 3, 0);

-- 商品表
drop table if exists kang_goods;
create table kang_goods (
  goods_id int unsigned auto_increment,
  name varchar(64) not null default '',
  image varchar(255) not null default '', -- 图像原图
  image_thumb varchar(255) not null default '', -- 图像缩略图(通常展示)
  quantity int unsigned not null default 0, -- 库存
  sku_id varchar(16) not null default '', -- 库存单位(个, 件, 台, 吨)
  upc varchar(255) not null default '', -- 通用商品代码
  price decimal(10, 2) not null default 0.0, -- 本店售价. 17
  market_price decimal(10, 2) not null default 0.0, -- 市场价
  tax_id int unsigned not null default 0, -- 税类型ID
  minimum int unsigned not null default 1, -- 最小起订数量
  subtract tinyint not null default 1, -- 是否减少库存
  stock_status_id int unsigned not null default 0, -- 库存状态ID
  shipping tinyint not null default 1, -- 是否允许配送
  date_available date not null default '0000-00-00', -- 供货日期
  length int unsigned not null default 0, -- 长
  width int unsigned not null default 0, -- 宽
  height int unsigned not null default 0, -- 高
  length_unit_id int unsigned not null default 0, -- 长度单位
  weight int unsigned not null default 0,-- 重量
  weight_unit_id int unsigned not null default 0, -- 重量的单位
  status tinyint not null default 1, -- 是否可用
  sort_number int not null default 0, -- 排序
  description text, -- 商品描述
  -- SEO优化
  meta_title varchar(255) not null default '',
  meta_keywords varchar(255) not null default '',
  meta_description varchar(1024) not null default '',

  brand_id int unsigned not null default 0, -- 所属品牌ID
  category_id int unsigned not null default 0, -- 所属分类ID
  type_id int UNSIGNED not null default 0, -- 所属类型iD

  created_at int not null default 0,
  updated_at int not null default 0,

  deleted tinyint not null default 0, -- 是否被删除, 逻辑删除, 允许还原
  primary key (goods_id),
  index (brand_id),
  index (category_id),
  index (tax_id),
  index (stock_status_id),
  index (length_unit_id),
  index (weight_unit_id),
  index (sort_number),
  index (name),
  index (price),
  unique key (upc)
) charset=utf8;

-- 库存单位
create table kang_sku
(
  sku_id int UNSIGNED NOT NULL AUTO_INCREMENT,
  title varchar(8) not null default '',
  sort_number int not null DEFAULT 0,
  PRIMARY KEY (sku_id),
  index (sort_number)
) CHARSET=utf8;
insert into kang_sku values (1, '个', 0);

-- 税类型
create table kang_tax (
  tax_id int unsigned auto_increment,
  title varchar(32) not null default '',
  primary key (tax_id)
) charset=utf8;
insert into kang_tax values (1, '免税产品');
insert into kang_tax values (2, '缴税产品');

-- 库存状态
create table kang_stock_status (
  stock_status_id int unsigned auto_increment,
  title varchar(32) not null default '',
  primary key (stock_status_id)
) charset=utf8;
insert into kang_stock_status values (1, '库存充足');
insert into kang_stock_status values (2, '1-3周');
insert into kang_stock_status values (3, '1-3天');
insert into kang_stock_status values (4, '脱销');
insert into kang_stock_status values (5, '预定');

-- 长度单位
create table kang_length_unit (
  length_unit_id int unsigned auto_increment,
  title varchar(32) not null default '',
  primary key (length_unit_id)
) charset=utf8;
insert into kang_length_unit values (1, '厘米');
insert into kang_length_unit values (2, '毫米');
insert into kang_length_unit values (3, '英寸');
insert into kang_length_unit values (4, '米');

-- 重量单位
create table kang_weight_unit (
  weight_unit_id int unsigned auto_increment,
  title varchar(32) not null default '',
  primary key (weight_unit_id)
) charset=utf8;
insert into kang_weight_unit values (1, '克');
insert into kang_weight_unit values (2, '千克');
insert into kang_weight_unit values (3, '斤(500克)');

-- 商品相册
create table kang_gallery (
  gallery_id int unsigned auto_increment,
  goods_id int unsigned not null default 0, -- 对应商品ID
  image varchar(255) not null default '', -- 商品原始图像
  image_small varchar(255) not null default '', -- 商品小图像
  image_medium varchar(255) not null default '', -- 商品中图像
  image_big varchar(255) not null default '', -- 商品大图像
  intro varchar(32) not null default '', -- 描述
  sort_number int not null default 0, -- 排序
  primary key (gallery_id),
  index (goods_id),
  index (sort_number)
) charset=utf8;


-- 商品类型
create table kang_type (
  type_id int unsigned auto_increment,
  type_title varchar(32) not null default '', -- 标题
  primary key (type_id)
) charset=utf8;

-- 商品属性输入类型
create table kang_attribute_type (
  attribute_type_id int unsigned auto_increment,
  attribute_type_title varchar(32) not null default '', -- 类型名
  primary key (attribute_type_id)
) charset=utf8;
insert into kang_attribute_type values (1, 'text'); -- 文本
insert into kang_attribute_type values (2, 'select'); -- 选择(单选)
insert into kang_attribute_type values (3, 'select-multi'); -- 选择(多选)

-- 商品属性
create table kang_attribute (
  attribute_id int unsigned auto_increment,
  attribute_title varchar(32) not null default '', -- 标题
  type_id int not null default 0, -- 所属商品类型ID
  attribute_type_id int not null default 0, -- 所属输入类型ID
  sort_number int not null default 0, -- 排序
  primary key (attribute_id),
  index (type_id),
  index (attribute_type_id)
) charset=utf8;

-- 属性预设值
create table kang_attribute_option (
  attribute_option_id int unsigned auto_increment,
  attribute_id int unsigned not null default 0, -- 所属属性ID
  option_value varchar(32) not null default '', -- 属性预设值
  primary key (attribute_option_id),
  index (attribute_id)
) charset=utf8;

-- 商品与属性关联
create table kang_goods_attribute (
  goods_attribute_id int unsigned auto_increment,
  goods_id int unsigned not null default 0, -- 商品ID
  attribute_id int unsigned not null default 0, -- 属性ID
  product_option tinyint not null default 0, -- 是否选项
  value varchar(255) not null default '', -- text输入类型属性的值
  primary key (goods_attribute_id),
  index (goods_id),
  index (attribute_id)
) charset=utf8;

-- 商品属性可用值表
create table kang_goods_attribute_option (
  goods_attribute_option_id int unsigned auto_increment,
  goods_attribute_id int unsigned not null default 0, -- 所属商品选项ID
  attribute_option_id int unsigned not null default 0, -- 对应选项预设值ID
  primary key (goods_attribute_option_id),
  index (goods_attribute_id),
  index (attribute_option_id)
) charset=utf8;


-- 货品表
create table kang_product (
  product_id int unsigned AUTO_INCREMENT,
  goods_id int UNSIGNED not null default 0,
  product_quantity int not null default 0,
  price_drift_id int not null DEFAULT 0,
  product_price decimal(10, 2) not null default 0.0,
  promoted tinyint not null default 0,
  enabled tinyint not null default 0,
  primary key (product_id),
  index (goods_id),
  index (price_drift_id)
) charset=utf8;

create table kang_price_drift (
  price_drift_id int UNSIGNED AUTO_INCREMENT,
  value VARCHAR(8) not null default '',
  title VARCHAR(16) not null default '',
  primary key(price_drift_id)
) CHARSET=utf8;
insert into kang_price_drift values (null, '+', '增加');
insert into kang_price_drift values (null, '-', '减少');
insert into kang_price_drift values (null, '=', '确定为');

-- 货品与已选属性的关系
create table kang_product_goods_attribute_option (
  product_goods_attribute_option_id int UNSIGNED AUTO_INCREMENT,
  product_id int UNSIGNED not null default 0,
  goods_attribute_option_id int UNSIGNED not null default 0,
  primary key (product_goods_attribute_option_id),
  index (product_id),
  index (goods_attribute_option_id)
) charset=utf8;

set @goods_id=7;

select p.product_id, p.promoted, pd.value price_drift, product_price, product_quantity, group_concat(a.attribute_title, ':', ao.option_value) as `option` from kang_product p left join kang_product_goods_attribute_option pgao using(product_id) left join kang_goods_attribute_option gao using(goods_attribute_option_id) left join kang_attribute_option ao using(attribute_option_id) left join kang_attribute a using(attribute_id) left join kang_price_drift pd using(price_drift_id) where p.goods_id=@goods_id and enabled=1 group by p.product_id;
select * from kang_product p left join kang_product_goods_attribute_option pgao using(product_id) left join kang_goods_attribute_option gao using(goods_attribute_option_id) left join kang_attribute_option ao using(attribute_option_id) left join kang_attribute a using(attribute_id) left join kang_price_drift pd using(price_drift_id) where p.goods_id=@goods_id and enabled=1\G

create table kang_cart
(
  cart_id int UNSIGNED AUTO_INCREMENT,
  member_id int UNSIGNED not null default 0,
  cart_title varchar(32),
  warelist text,
  primary key (cart_id),
  unique key (member_id)
) charset=utf8;

create table kang_address
(
  address_id int UNSIGNED AUTO_INCREMENT,
  name varchar(32) not null default '',
  telephone varchar(16) not null default '',
  company varchar(64) not null default '',
  region_one_id int UNSIGNED not null default 0,
  region_two_id int UNSIGNED not null default 0,
  region_three_id int UNSIGNED not null default 0,
  address varchar(255) not null default '',
  postcode varchar(16) not null default '',
  is_default tinyint not null default 0,
  member_id int UNSIGNED not null default 0,
  primary key (address_id),
  index (member_id),
  index (region_one_id),
  index (region_two_id),
  index (region_three_id)
) charset=utf8;


create table kang_payment
(
  payment_id int UNSIGNED AUTO_INCREMENT,
  `key` varchar(32) not null default '', -- 唯一标志
  title varchar(32) not null default '',
  enabled tinyint not null default 1, -- 是否启用
  sort_number int not null default 0, -- 排序
  primary key (payment_id)
) charset=utf8;

create table kang_shipping
(
  shipping_id int UNSIGNED AUTO_INCREMENT,
  `key` varchar(32) not null default '', -- 唯一标志
  title varchar(32) not null default '',
  enabled tinyint not null default 1, -- 是否启用
  sort_number int not null default 0, -- 排序
  primary key (shipping_id)
) charset=utf8;

drop table if exists kang_order;
create table kang_order
(
  order_id int UNSIGNED AUTO_INCREMENT,
  order_sn varchar(64) not null DEFAULT '',
  member_id int UNSIGNED not null DEFAULT 0,
  created_at int not NULL  default 0, -- 订单创建时间
  total_quantity int not null default 0, -- 总商品数量
  total_price decimal(10, 2) not null default 0.0, -- 总价格
  order_status_id int UNSIGNED not null default 0 , -- 未确认, 已确认, 已取消, 已删除, 已完成
  ensure_time int NOT NULL default 0, -- 确认时间
  cancel_time int NOT NULL default 0, -- 取消时间
  remove_time int NOT NULL default 0, -- 删除时间
  shipping_status_id int UNSIGNED not null default 0, -- 配送状态, 未发货, 已发货, 已收货
  send_time int UNSIGNED not NULL default 0, -- 发货时间
  received_time int UNSIGNED not NULL default 0, -- 收获时间
  shipping_sn varchar(64) not null default '', -- 订单编号
  payment_status_id int UNSIGNED not null default 0, -- 支付状态
  pay_time int not null default 0, -- 支付时间
  address_id int not null default 0, -- 收获地址
  primary key (order_id),
  unique index (order_sn),
  index (member_id),
  index (order_status_id),
  index (shipping_status_id),
  index (payment_status_id),
  index (address_id)
) charset=utf8;

create table kang_order_ware
(
  order_ware_id int UNSIGNED AUTO_INCREMENT,
  order_id int UNSIGNED not null default 0,
  goods_id int UNSIGNED not null default 0,
  product_id int UNSIGNED not null default 0,
  buy_quantity int not NULL default 0,
  buy_price DECIMAL(10, 2) not null default 0.0, -- 购买时的价格
  PRIMARY KEY (order_ware_id),
  index (order_id),
  index (goods_id),
  index (product_id)
) charset=utf8;

create table kang_order_status
(
  order_status_id int UNSIGNED AUTO_INCREMENT,
  order_status_title varchar(32) not null default '',
  PRIMARY KEY (order_status_id)
) charset=utf8;
insert into kang_order_status values
  (null, '未确认'),(null, '已确认'),(null, '已取消'),(null, '已删除'),(null, '已完成');

create table kang_payment_status
(
  payment_status_id int UNSIGNED AUTO_INCREMENT,
  payment_status_title varchar(32) not null default '',
  PRIMARY KEY (payment_status_id)
) charset=utf8;
insert into kang_payment_status VALUES
  (null, '未支付'), (null, '已支付');
create table kang_shipping_status
(
  shipping_status_id int UNSIGNED AUTO_INCREMENT,
  shipping_status_title varchar(32) not null default '',
  PRIMARY KEY (shipping_status_id)
) charset=utf8;
insert into kang_shipping_status values
  (null, '未发货'), (null, '已发货'), (null, '已收货');

# create table order_log();-- 记录订的操作日志

# 管理员表
create table kang_admin
(
  user_id int UNSIGNED not null AUTO_INCREMENT,
  username varchar(32) not null default '',
  password varchar(64) not null default '',
  password_salt varchar(8) not null default '',
  primary key (user_id),
  index (username)
) charset=utf8;

insert into kang_admin values (null, 'kang', md5(concat('hellokang', '9527')), '9527');

CREATE TABLE IF NOT EXISTS `kang_access` (
  `role_id` smallint(6) unsigned NOT NULL,
  `node_id` smallint(6) unsigned NOT NULL,
  `level` tinyint(1) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  primary key (role_id, node_id),
  KEY `groupId` (`role_id`),
  KEY `nodeId` (`node_id`)
) CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `kang_node` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL, -- 模块, 控制器, 动作标志
  `title` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `remark` varchar(255) DEFAULT NULL,
  `sort` smallint(6) unsigned DEFAULT NULL,
  `pid` smallint(6) unsigned NOT NULL, -- 所属的上级
  `level` tinyint(1) unsigned NOT NULL, -- 1模块, 2控制器, 3动作
  PRIMARY KEY (`id`),
  KEY `level` (`level`),
  KEY `pid` (`pid`),
  KEY `status` (`status`),
  KEY `name` (`name`)
) CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `kang_role` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `pid` smallint(6) DEFAULT NULL,
  `status` tinyint(1) unsigned DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `status` (`status`)
) CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `kang_role_user` (
  `role_id` mediumint(9) unsigned DEFAULT NULL,
  `user_id` char(32) DEFAULT NULL,
  primary key (role_id, user_id),
  KEY `group_id` (`role_id`),
  KEY `user_id` (`user_id`)
) CHARSET=utf8;