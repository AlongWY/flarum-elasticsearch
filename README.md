# Flarum 论坛中文搜索插件
Flarum 论坛中文搜索插件 - 基于 elasticsearch 搜索引擎开发

### 安装插件
`composer require alongwy/flarum-elasticsearch`
> 如果遇到权限问题， 则使用 `sudo` 进行安装, 安装完成后对相关文件权限进行设置

``
sudo chown -R www-data:www-data flarum/
``
### 更新

```sh
composer update alongwy/flarum-elasticsearch
```

### Links

- [Packagist](https://packagist.org/packages/alongwy/flarum-elasticsearch)


### 其他问题
Flarum 所有文件权限最好是 apache 或 nginx 有权限的用户才行，例如 *www-data* 用户，如果有各种权限问题，就把 flarum 的文件设置为相关的用户和用户组即可  
