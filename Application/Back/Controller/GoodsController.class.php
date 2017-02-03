<?php


namespace Back\Controller;

use Think\Page;
use Think\Upload;
use Think\Image;

class GoodsController extends CommonController
{

    public function addAction()
    {

        if (IS_POST) {

            $model = D('Goods');
            if ($model->create()) {// 校验
                $goods_id = $model->add();// 添加

                // 添加商品相册数据
                $modelGallery = M('Gallery');
                // 形成带有goods_id字段的每条记录数据
                $galleryList = [];
                foreach(I('post.galleries') as $value) {
                    $value['goods_id'] = $goods_id;
                    $galleryList[] = $value;
                }
                // 批量插入
                $modelGallery->addAll($galleryList);

                // 处理商品属性数据
                // type_id自动处理
                $modelAttribute = M('Attribute');
                $modelAttributeType = M('AttributeType');
                $modelGoodsAttribute = M('GoodsAttribute');
                $modelGoodsAttributeOption = M('GoodsAttributeOption');
                foreach(I('post.attribute', []) as $attribute_id => $value) {
                    // 判断当前的属性类型
                    $attribute_type_id = $modelAttribute->where(['attribute_id'=>$attribute_id])->getField('attribute_type_id');
                    $attributeType = $modelAttributeType->where(['attribute_type_id'=>$attribute_type_id])->getField('attribute_type_title');

                    switch ($attributeType) {
                        case 'text':// 文本输入
                            $data = [
                                'goods_id'  => $goods_id,
                                'attribute_id'  => $attribute_id,
                                'value' => $value,
                            ];
                            if ($modelGoodsAttribute->create($data)) {
                                $modelGoodsAttribute->add();
                            }
                            break;
                        case 'select': // 单选
                            // 先插入goods_attribute
                            $data = [
                                'goods_id'  => $goods_id,
                                'attribute_id'  => $attribute_id,
                            ];
                            if ($modelGoodsAttribute->create($data)) {
                                $goods_attribute_id = $modelGoodsAttribute->add();
                                // 插入goods_attribute_option表
                                $data = [
                                    'goods_attribute_id'  => $goods_attribute_id,
                                    'attribute_option_id'  => $value,
                                ];
                                $modelGoodsAttributeOption->add($data);
                            }
                            break;
                        case 'select-multi':// 多选
                            // 先插入goods_attribute
                            $data = [
                                'goods_id'  => $goods_id,
                                'attribute_id'  => $attribute_id,
//                                判断是否存在于is_option数组中
                                'product_option' => in_array($attribute_id, I('post.is_option', [])) ? 1 : 0,
                            ];
                            if ($modelGoodsAttribute->create($data)) {
                                $goods_attribute_id = $modelGoodsAttribute->add();
                                // 插入goods_attribute_option表
                                $rows = array_map(function($v) use($goods_attribute_id) {
                                    return [
                                        'goods_attribute_id'    =>$goods_attribute_id,
                                        'attribute_option_id'   => $v,
                                    ];
                                }, $value);

                                $modelGoodsAttributeOption->addAll($rows);
                            }
                            break;

                    }
                }


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

            // 获取对应的数据
            $this->assign('sku_list', M('Sku')->select());
            $this->assign('tax_list', M('Tax')->select());
            $this->assign('stock_status_list', M('StockStatus')->select());
            $this->assign('length_unit_list', M('LengthUnit')->select());
            $this->assign('weight_unit_list', M('WeightUnit')->select());
            $this->assign('brand_list', M('Brand')->select());
            $this->assign('category_list', D('Category')->getTreeList());
            $this->assign('type_list', M('Type')->select());
            // 获取属性数据


            $this->display('set');
        }
    }

    /**
     * 更新
     */
    public function editAction()
    {

        $model = D('Goods');
        if (IS_POST) {
            if ($model->create()) {// 校验
                $model->save();// 更新

                // 新相册图像的添加
//                旧相册图像相关信息的更新
                $modelGallery = M('Gallery');
                // 形成带有goods_id字段的每条记录数据
                $newGalleryList = [];
                foreach(I('post.galleries') as $key=>$value) {
                    // 判断当前的相册新增还是更新
                    if(isset($value['gallery_id'])) {
                        // 是gallery_id, 是更新
                        $modelGallery->save($value);
                    } else {
                        $value['goods_id'] = I('post.goods_id');
                        $newGalleryList[] = $value;
                    }
                }
                // 批量插入
                $modelGallery->addAll($newGalleryList);

                // 处理商品属性数据
                // type_id自动处理
                $goods_id = I('post.goods_id');
                $modelAttribute = M('Attribute');
                $modelAttributeType = M('AttributeType');
                $modelGoodsAttribute = M('GoodsAttribute');
                $modelGoodsAttributeOption = M('GoodsAttributeOption');

                foreach(I('post.attribute', []) as $goods_attribute_id => $value) {
                    // 判断当前的属性类型
                    $attributeType = $modelAttributeType
                        ->field('attribute_type_title')
                        ->join('left join __ATTRIBUTE__ a using(attribute_type_id)')
                        ->join('left join __GOODS_ATTRIBUTE__ ga using(attribute_id)')
                        ->where(['goods_attribute_id'=>$goods_attribute_list])
                        ->find();
                    $attributeType = $attributeType['attribute_type_title'];

                    switch ($attributeType) {
                        case 'text':// 文本输入
                            $data = [
                                'goods_attribute_id'  => $goods_attribute_id,
                                'value' => $value,
                            ];
                            if ($modelGoodsAttribute->create($data)) {
                                $modelGoodsAttribute->save();
                            }
                            break;
                        case 'select': // 单选
                            $modelGoodsAttributeOption->where([
                                'goods_attribute_id'  => $goods_attribute_id,
                            ])->save([
                                'attribute_option_id'  => $value,
                            ]);

                            break;
                        case 'select-multi':// 多选

                            // 插入goods_attribute_option表
                            $rows = array_map(function($v) use($goods_attribute_id) {
                                return [
                                    'goods_attribute_id'    =>$goods_attribute_id,
                                    'attribute_option_id'   => $v,
                                ];

                            }, $value);
                            $modelGoodsAttributeOption->add($row);

                            break;

                    }
                }

                $this->redirect('list');// 重定向到列表动作
            } else {
                // 将错误信息存储到session中, 便于下个页面输出错误消息
                session('message', ['error'=>1, 'errorInfo'=>$model->getError()]);
                session('data', $_POST);
                $this->redirect('edit', ['goods_id'=>I('post.goods_id')]); // 重定向到添加
            }
        } else {
            $this->assign('message', session('message'));
            session('message', null);// 删除该信息
            // 获取当前编辑的内容, 如果是编辑错误,则显示错误的内容, 如果是没有错误, 则显示原始数据内容
            $data = is_null(session('data')) ? $model->find(I('get.goods_id')) : session('data');
            $this->assign('data', $data);
            session('data', null);

           // 获取对应的数据
            $this->assign('sku_list', M('Sku')->select());
            $this->assign('tax_list', M('Tax')->select());
            $this->assign('stock_status_list', M('StockStatus')->select());
            $this->assign('length_unit_list', M('LengthUnit')->select());
            $this->assign('weight_unit_list', M('WeightUnit')->select());
            $this->assign('brand_list', M('Brand')->select());
            $this->assign('category_list', D('Category')->getTreeList());
           // 商品相册图像
            $this->assign('gallery_list', M('Gallery')->where(['goods_id'=>I('get.goods_id')])->select());
            $this->assign('type_list', M('Type')->select());
            // 商品类型下全部的商品属性
            $attribute_list = D('Attribute')
                ->alias('a')
                ->join('left join __ATTRIBUTE_TYPE__ at using(attribute_type_id)')
                ->relation(true)
                ->where(['type_id'=>$data['type_id']])
                ->select();
            // 当前商品的以选的属性或者填写的值
            $goods_attribute_list = D('GoodsAttribute')
                ->alias('ga')
                ->relation(true)
                ->where(['goods_id'=>$data['goods_id']])
                ->select();
            // 合并
            $attribute_merge_list = [];
            foreach($attribute_list as $attribute ) {
               foreach($goods_attribute_list as $goods_attribute) {
                   if ($attribute['attribute_id'] == $goods_attribute['attribute_id']) {
                       // 获取被选中的id的集合
                       $goods_attribute['checked_list'] = array_map(function($v) {
                           return $v['attribute_option_id'];
                       }, $goods_attribute['goodsOptionList']);
                       $attribute_merge_list[$attribute['attribute_id']] = array_merge($attribute, $goods_attribute);
                       break ;
                   }
               }
            }
//            dump($attribute_merge_list);
            $this->assign('attribute_merge_list', $attribute_merge_list);

           $this->display('set');
       }
    }

    public function listAction()
    {
        $model = M('Goods');

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
                $model = M('Goods');
                $model->where(['goods_id'=>['in', I('post.selected')]])->delete();
                break;
        }
        $this->redirect('list');
    }

// 开发数据接口
    public function ajaxAction()
{

    switch (I('request.operate', '')) {
        case 'getAttrList':
            $rows = D('Attribute')
                ->alias('a')
                ->join('left join __ATTRIBUTE_TYPE__ at using(attribute_type_id)')
                ->relation(true)
                ->where(['type_id'=>I('request.type_id')])
                ->select();
            if ($rows) {
                $this->ajaxReturn(['error'=>0, 'rows'=>$rows]);
            } else {
                $this->ajaxReturn(['error'=>1]);
            }
            break;
        case 'imageUpload':
            $toolUpload = new Upload();
            $toolUpload->exts = ['png', 'jpeg', 'jpg', 'gif'];// 允许类型
            $toolUpload->maxSize = 1 * 1024 * 1024;// 1M
            $toolUpload->rootPath = APP_PATH . 'Upload/';// 上传的根目录
            $toolUpload->savePath = 'Goods/';// 保证目录存在
            // 执行上传
            $uploadInfo = $toolUpload->uploadOne($_FILES['imageAjax']);
            if ($uploadInfo) {
                // 成功, 按照需要的格式进行响应
                $image = $uploadInfo['savepath'] . $uploadInfo['savename'];
//                    创建public/Thumb/日期
                $toolImage = new Image();
                if (! is_dir ('./Public/Thumb/' . $uploadInfo['savepath'])) {
                    mkdir ('./Public/Thumb/' . $uploadInfo['savepath'], 0764, true);
                }
                $toolImage
                    ->open(APP_PATH . 'Upload/' . $image)
                    // 出现对图像的操作(裁剪, 缩略图, 水纹)
                    ->thumb(300, 340, Image::IMAGE_THUMB_FILLED)// 缩略图
                    ->save('./Public/Thumb/' . $image);
                // 原始文件名, 缩略图文件名
                // 制作响应数据
                $this->ajaxReturn(['error'=>0, 'imageAjax'=>['image'=>$image, 'image_thumb'=>$image, 'thumbUrl'=>$image]]);
            }
            break;

        case 'galleriesUpload':
            $toolUpload = new Upload();
            $toolUpload->exts = ['png', 'jpeg', 'jpg', 'gif'];// 允许类型
            $toolUpload->maxSize = 1 * 1024 * 1024;// 1M
            $toolUpload->rootPath = APP_PATH . 'Upload/';// 上传的根目录
            $toolUpload->savePath = 'Gallery/';// 保证目录存在
            // 执行上传
            $uploadInfo = $toolUpload->uploadOne($_FILES['galleriesAjax']);
            if ($uploadInfo) {
                // 成功, 按照需要的格式进行响应
                $image = $uploadInfo['savepath'] . $uploadInfo['savename'];
//                    创建public/Thumb/日期
                $toolImage = new Image();
                if (! is_dir ('./Public/Thumb/' . $uploadInfo['savepath'])) {
                    mkdir ('./Public/Thumb/' . $uploadInfo['savepath'], 0764, true);
                }
                // 创建大中小三种缩略图
                $toolImage->open(APP_PATH . 'Upload/' . $image);
                // 大缩略图 填充
                $bigImage = $uploadInfo['savepath'] . 'big-' . $uploadInfo['savename'];
                $toolImage->thumb(800, 800, Image::IMAGE_THUMB_FILLED)->save('./Public/Thumb/' . $bigImage);
                // 中缩略图 填充
                $mediumImage = $uploadInfo['savepath'] . 'medium-' . $uploadInfo['savename'];
                $toolImage->thumb(300, 300, Image::IMAGE_THUMB_FILLED)->save('./Public/Thumb/' . $mediumImage);
                // 小缩略图 填充
                $smallImage = $uploadInfo['savepath'] . 'small-' . $uploadInfo['savename'];
                $toolImage->thumb(60, 60, Image::IMAGE_THUMB_FILLED)->save('./Public/Thumb/' . $smallImage);
                // 原始文件名, 缩略图文件名
                // 制作响应数据
                $this->ajaxReturn([
                    'error'=>0,
                    'image'=>$image,
                    'image_small'=>$smallImage,
                    'image_medium'=>$mediumImage,
                    'image_big'=>$bigImage,
                    'key'=>strchr($uploadInfo['savename'], '.', true),
                    'savepath'=>$uploadInfo['savepath'],
                    'ext' => strrchr($uploadInfo['savename'], '.'),
                ]);
            }
            break;

        case 'galleryRemove':
            // 判断当前是否传输了gallery_id
            $gallery_id = I('request.gallery_id', null);
            if (is_null($gallery_id)) {
                $image = I('request.key') . I('request.ext');
                $savepath = I('request.savepath');
            } else {
                // gallery_ID传递
                $imageLong = M('Gallery')->where(['gallery_id'=>$gallery_id])->getField('image');
                $image = substr($imageLong, strrpos($imageLong, '/')+1);
                $savepath = substr($imageLong, 0, strrpos($imageLong, '/')+1);

                // 删除记录
                M('Gallery')->delete($gallery_id);
            }
            //删图像文件
            @unlink(APP_PATH . 'Upload/' . $savepath . $image);// 上传的原图
            @unlink('./Public/Thumb/' . $savepath . 'big-' . $image);// 大图
            @unlink('./Public/Thumb/' . $savepath . 'medium-'. $image);// 中图
            @unlink('./Public/Thumb/' . $savepath . 'small-' . $image);// 小图

            $this->ajaxReturn(['error'=>0]);
            break;

        case 'addProduct':
            // 先生成product记录
            $modelProduct = M('Product');
            $product_id = $modelProduct->add(I('request.'));

            // 生成product_goods_attribute_option
            $modelPGAO = M('ProductGoodsAttributeOption');
            $rows = array_map(function($goods_attribute_option_id)  use($product_id) {
                return [
                    'product_id' => $product_id,
                    'goods_attribute_option_id' => $goods_attribute_option_id,
                ];
            }, I('request.option', []));
            $modelPGAO->addAll($rows);

            $product['product_id'] = $product_id;
            $product = array_merge($product, I('request.'));
            // 通过所选的选项ID获取选项内容
            $modelGAO = M('GoodsAttributeOption');
            foreach($product['option'] as $goods_attribute_option_id) {
                $row = $modelGAO
                    ->join('left join __ATTRIBUTE_OPTION__ using(attribute_option_id)')
                    ->find($goods_attribute_option_id);
                $product['optionList'][] = $row;
            }
            $this->assign('product', $product);

            $this->assign('priceDriftList', M('PriceDrift')->select());
            $this->display('product_row');
            break;
        default:
            $this->ajaxReturn(['error'=>1, 'errorInfo'=>'操作错误']);
            break;
    }
}


    /**
     * 货品操作
     */
    public function productAction()
    {
        // 获取当前商品的货品选项属性列表
        $goods_id = I('get.goods_id');
        $modelGoodsAttribute = M('GoodsAttribute');
        $optionList = $modelGoodsAttribute
            ->alias('ga')
            ->join('left join __ATTRIBUTE__ a using(attribute_id)')
            ->where(['goods_id'=>$goods_id, 'product_option'=>'1'])
            ->select();

        // 遍历当前的商品货品属性, 获取当前货品属性对应已选的属性选项值
        $modelGoodsAttributeOption = M('GoodsAttributeOption');
        foreach($optionList as $key=>$option) {
            $valueList = $modelGoodsAttributeOption
                ->alias('gao')
                ->join('left join __ATTRIBUTE_OPTION__ ao using(attribute_option_id)')
                ->where(['goods_attribute_id'=>$option['goods_attribute_id']])
                ->select();
            $optionList[$key]['valueList'] = $valueList;
        }
        $this->assign('optionList', $optionList);

        $this->assign('priceDriftList', M('PriceDrift')->select());

        $this->assign('goods_id', I('get.goods_id'));

        // 已有货品列表即可
        $productList = M('Product')
            ->where(['goods_id'=>I('get.goods_id')])
            ->select();
        foreach($productList as $key=>$product) {
            $rows = M('ProductGoodsAttributeOption')
                ->join('left join __GOODS_ATTRIBUTE_OPTION__ using(goods_attribute_option_id)')
                ->join('left join __ATTRIBUTE_OPTION__ using(attribute_option_id)')
                ->where(['product_id'=>$product['product_id']])
                ->select();
            $productList[$key]['optionList'] = $rows;
        }
        $this->assign('productList', $productList);


        $this->display();
    }

}