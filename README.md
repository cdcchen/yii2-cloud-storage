# yii2-cloud-storage 组件使用说明

此组件主要功能为两部分：

- 将目前主流云存储平台的接口封闭，以更加方便的使用。
- 添加云存储版的`UploadFile`，`saveAs`方法将上传文件保存到云存储中。


## 使用步骤

### 第一步：在components中添加组件配置

```php
'storage' => [
    'class' => 'cdcchen\yii\cloudstorage\UpYunStorage',
    'isImageBucket' => true,
    'endpoint' => 'v2.api.upyun.com',
    'bucket' => 'test',
    'username' => 'test',
    'password' => '123123',
    'domain' => 'http://test.b0.upaiyun.com',
    'autoGenerateFilename' => true,
    'pathFormat' => '{year}/{month}/{day}',
    'filenameFormat' => '{timestamp}-{uniqid}',
]
```



### 第二步：在action中调用storage组件进行文件操作

#### 获取storage组件实例

```php
/* @var \cdcchen\yii\cloudstorage\Storage $storage */
$storage = Yii::$app->get('storage');
```


## 云存储文件操作方法说明

### 上传文件

> write方法第一个参数$filename可以接受一个本地文件路径，也可以接受一个文件的内容。
> 
> 若第二个参数为`null`，则组件会按照`pathFormat`和`filenameFormat`的配置来自动生成文件路径及文件名。
> 

**自动生成文件路径**

```php
$filename = Yii::getAlias('@runtime/chen.jpg');
$info = $storage->write($filename);

```

**手动指定文件路径**

```php
$filename = Yii::getAlias('@runtime/chen.jpg');
$filepath = '/test/a.jpg';
$info = $storage->write($filename, $filepath);

```

**返回数据结构如下：**

图片文件

```php
Array
(
    [url] => http://test.b0.upaiyun.com/2016/05/04/1462332949-57296e153ab67.jpeg
    [file] => /avatar/chendong/2016/05/04/1462332949-57296e153ab67.jpeg
    [path] => /avatar/chendong/2016/05/04
    [name] => 1462332949-57296e153ab67.jpeg
    [width] => 479
    [height] => 555
    [type] => JPEG
    [frames] => 1
)
```

非图片文件

```php
Array
(
    [url] => http://test.b0.upaiyun.com/avatar/2016/05/04/1462333056-57296e80bbfa2.doc
    [file] => /avatar/chendong/2016/05/04/1462333056-57296e80bbfa2.doc
    [path] => /avatar/chendong/2016/05/04
    [name] => 1462333056-57296e80bbfa2.doc
)
```


### 读取文件(下载文件)

```php
$filename = '/test/a.jpg';
$content = $storage->read($filename);
```

返回值`$content`为`$filename`的内容。


### 删除文件

```php
$filename = '/test/a.jpg';
$result = $storage->delete($filename);
```

删除成功返回true。


## UploadFile使用说明

`cdcchen\yii\cloudstorage\UploadFile`继承自`yii\web\UploadFile，在此基础上又添加了`upload`方法。

使用方法同`yii\web\UploadFile`，如果自动生成文件路径及文件名，直接使用`upload`方法。这样做是为了保证saveAs的兼容性和一致性。