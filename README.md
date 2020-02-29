<h1 align="center">Laravel mail aliyun</h1>
<hr>
<p align="center">阿里云邮件发送SDK - Laravel 版</p>

## 安装

```shell
composer require vagh/laravel-aliyun-mailer
```

成功安装后会自动注册包至 `ServiceProvider` 中。

## 配置
在你的 `config/service.php` 文件中添加以下配置：

```php
'ali_mail' => [
    // 阿里云用户AccessKey
    'access_key' => '****',
    // 阿里云用户AccessSecret
    'access_key_secret' => '****',
    // 对应资源区域
    'region_id' => 'cn-hangzhou',
    // 发信人昵称 长度小于15个字符
    'from_alias' => 'BigYu',
    // 发件人地址 一般在邮件后台设置好
    'from_address' => '****@mail.vagh.cn',
],
```

附：[阿里云邮件API](https://help.aliyun.com/document_detail/29444.html)

## 使用

修改你的 `.env` 文件：

```bash
MAIL_DRIVER=ali_mail
```

附：[Laravel 官方邮件发送文档(6.x)](https://laravel.com/docs/6.x/mail)