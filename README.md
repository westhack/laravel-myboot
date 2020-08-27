<p align="center">
  <a href="http://docs.xinrennet.com/">
    <img width="200" src="http://game.cdn.limaopu.com/myboot-logo.png">
  </a>
</p>

## 简介

myboot-laravel 模块化脚手架。

myboot 是一套前后端完整的解决方案，前端页面请移步：

[myboot-vue ](https://github.com/westhack/myboot-vue)

### 文档地址

[http://docs.xinrennet.com/](http://docs.xinrennet.com/)

### 线上 Demo

[http://laravel-vue.xinrennet.com/](http://laravel-vue.xinrennet.com/)

## 环境

- PHP >= 7.2.5
- laravel framework >= 7.0
- mysql >= 5.7

## 安装

```
# clone myboot-laravel
$ git clone https://github.com/westhack/myboot-laravel.git
$ cd myboot-laravel

# 安装依赖
$ composer install

# 设置配置
$ cp .env.dusk.local .env

# 导入数据库
$ mysql -u root -p laravel-vue < ./database/migrations/laravel-vue.sql

# 运行
$ php artisan serve --host=localhost --port=8002
```


