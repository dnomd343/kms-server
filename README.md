# KMS服务器

快速部署的KMS服务器，提供针对Windows和Office的激活服务，同时内置了各个版本的激活密钥与命令，支持Docker容器化部署，在[Docker Hub](https://hub.docker.com/repository/docker/dnomd343/kms-server)或[Github Package](https://github.com/dnomd343/TProxy/pkgs/container/kms-server)可以查看已构建的镜像。

## 使用方法

以 `kms.343.re` 为例，在成功部署KMS服务以后，你可以通过[网页](https://kms.343.re/)或者命令行获取激活密钥。

```
# 输出操作说明
shell> curl kms.343.re

# 输出Windows的KMS密钥
shell> curl kms.343.re/win

# 输出Windows Server的KMS密钥
shell> curl kms.343.re/win-server

# 输出Office激活说明
shell> curl kms.343.re/office

```

部署完成后，需要KMS服务的地方填入 `kms.343.re` 即可激活。

## 镜像获取

`kms-server` 可以从多个镜像源拉取，其数据完全相同，国内用户建议首选阿里云镜像。

```
# Docker Hub
shell> docker pull docker.io/dnomd343/kms-server

# Github Package
shell> docker pull ghcr.io/dnomd343/kms-server

# 阿里云个人镜像
shell> docker pull registry.cn-shenzhen.aliyuncs.com/dnomd343/kms-server
```

镜像对外暴露 `1688/tcp` 与 `1689/tcp` 端口，前者用于KMS激活服务，后者用于获取KMS激活密钥。

## 部署流程

**检查防火墙**

服务器的1688端口必须能接受来自外网的访问，务必检查是否被防火墙拦截。

如果开启了 `firewalld` 防火墙服务，使用以下命令放行1688端口

```
shell> firewall-cmd --zone=public --add-port=1688/tcp --permanent
shell> firewall-cmd --reload
shell> firewall-cmd --list-ports
```

如果开启了 `ufw` 防火墙服务，使用以下命令放行1688端口

```
shell> ufw allow 1688/tcp
shell> ufw status
```

一些云服务商可能会在网页端控制台提供防火墙服务，请在对应页面开放 `1688/tcp` 端口。

### Docker方式（推荐）

**1. 配置Docker环境**

使用以下命令确认Docker环境

```
# 若正常输出则跳过本步
shell> docker --version
···Docker版本信息···
```

使用以下命令安装Docker

```
# RH系
shell> sudo yum update
···
# Debian系
shell> sudo apt update && sudo apt upgrade
···
# 使用Docker官方脚本安装
shell> sudo wget -qO- https://get.docker.com/ | bash
···
# 安装成功后将输出Docker版本信息
shell> docker --version
Docker version ···, build ···
```

**2. 启动KMS服务**

启动容器并映射端口

```
# 映射容器1688与1689端口到宿主机，容器路径可替换为上述其他源
shell> docker run -d --name kms -p 1688:1688 -p 1689:1689 dnomd343/kms-server
# 查看容器状态
shell> docker ps -a
CONTAINER ID   IMAGE                    COMMAND           CREATED          STATUS        PORTS     NAMES
···
```

若服务器1689端口未配置防火墙，在浏览器输入 `http://服务器IP:1689/` 即可访问Web主页。

```
# 测试容器是否正常工作
shell> curl 127.0.0.1:1689/win
···不同版本Windows的KMS密钥···
```

**3. 配置反向代理**

将用于KMS服务的域名DNS解析到当前服务器，这里使用Nginx作为示例，其他Web服务原理类似。

```
# 进入Nginx配置目录
shell> cd /etc/nginx/conf.d
# 下载配置文件
shell> wget https://raw.githubusercontent.com/dnomd343/kms-server/master/conf/nginx/docker.conf -O kms.conf
# 修改配置文件中域名、证书、端口等信息
shell> vim kms.conf
```

如果你的网络无法正常访问Github，将下述内容写入配置文件亦可。

```
server {
    listen 80;
    listen [::]:80;
    server_name kms.343.re; # 改为自己的域名
    location / {
        if ($http_user_agent !~* (curl|wget)) {
            return 301 https://$server_name$request_uri;
        }
        proxy_set_header Host $http_host;
        proxy_pass http://127.0.0.1:1689;
    }
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name kms.343.re; # 改为自己的域名
    ssl_certificate /etc/ssl/certs/343.re/fullchain.pem; # 改为自己的证书
    ssl_certificate_key /etc/ssl/certs/343.re/privkey.pem;
    
    gzip on;
    gzip_buffers 32 4K;
    gzip_comp_level 6;
    gzip_min_length 100;
    gzip_types application/javascript text/css text/xml;
    gzip_disable "MSIE [1-6]\.";
    gzip_vary on;

    location / {
        proxy_set_header Host $http_host;
        proxy_pass http://127.0.0.1:1689;
    }
}
```

重启Nginx服务

```
shell> nginx -s reload
```

### 常规方式（不推荐）

此方式较为繁琐且可能存在版本兼容问题，不熟悉Linux操作的用户建议使用上述Docker方式。

**1. 拉取源码**

首先拉取仓库到服务器上，这里以 `/var/www/kms-server` 为例

```
shell> cd /var/www
shell> git clone https://github.com/dnomd343/kms-server.git
Cloning into 'kms-server'...
···
Unpacking objects: 100% ··· done.
```

**2. 环境检查**

确定你的服务器上有PHP环境，同时有 `curl` 工具

```
shell> php -v
···PHP版本信息···

shell> curl --version
···curl版本信息···
```

确认PHP-FPM正常运行

```
shell> systemctl | grep fpm
  php7.3-fpm.service            loaded active running   The PHP 7.3 FastCGI Process Manager
```

**3. 配置Web服务**

配置网页服务器代理，需要额外占用除80与443之外的一个端口，默认为 `1689/tcp` ，可按需修改。

将用于KMS服务的域名DNS解析到当前服务器，这里使用Nginx作为示例，其他Web服务原理类似。

```
# 进入nginx配置目录
shell> cd /etc/nginx/conf.d

# 从代码仓库复制配置文件
shell> cp /var/www/kms-server/conf/nginx/kms.conf ./

# 修改配置文件中域名、证书、端口等信息
shell> vim kms.conf
```

配置文件内容如下

```
server {
    listen 80;
    listen [::]:80;
    server_name kms.343.re; # 改为自己的域名
    location / {
        if ($http_user_agent !~* (curl|wget)) {
            return 301 https://$server_name$request_uri;
        }
        proxy_set_header Host $http_host;
        proxy_pass http://127.0.0.1:1689;
    }
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name kms.343.re; # 改为自己的域名
    ssl_certificate /etc/ssl/certs/343.re/fullchain.pem; # 改为自己的证书
    ssl_certificate_key /etc/ssl/certs/343.re/privkey.pem;
    
    gzip on;
    gzip_buffers 32 4K;
    gzip_comp_level 6;
    gzip_min_length 100;
    gzip_types application/javascript text/css text/xml;
    gzip_disable "MSIE [1-6]\.";
    gzip_vary on;

    location / {
        proxy_set_header Host $http_host;
        proxy_pass http://127.0.0.1:1689;
    }
}

server {
    listen 1689;
    root /var/www/kms-server;

    location / {
        set $query_param $query_string;
        if ($http_user_agent ~* (curl|wget)) {
            set $query_param $query_param&cli=true;
        }
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000; # php-fpm接口
        fastcgi_param QUERY_STRING $query_param;
        fastcgi_param SCRIPT_FILENAME /var/www/kms-server/backend/route.php;
    }

    location /assets {}
}
```

其中PHP-FPM接口在各系统上不同

```
# RH系一般为本地9000端口
shell> netstat -tlnp | grep 9000
tcp        0      0 127.0.0.1:9000          0.0.0.0:*               LISTEN      783/php-fpm: master
# Debian系一般为sock方式
shell> ls /var/run/php/
php7.3-fpm.pid  php7.3-fpm.sock
```

对应Nginx配置如下
```
# RH系
fastcgi_pass 127.0.0.1:9000;
# Debian系
fastcgi_pass unix:/var/run/php/php7.3-fpm.sock;
```

重启Nginx服务

```
shell> nginx -s reload
```

**4. 配置vlmcsd服务**

在vlmcsd的[Github页面](https://github.com/Wind4/vlmcsd/releases)获取最新release包

```
# 下载最新release并解压
shell> wget https://github.com/Wind4/vlmcsd/releases/download/svn1113/binaries.tar.gz
shell> tar xf binaries.tar.gz

# 不同架构主机选择不同文件，以下为常见示例
# x86-64架构
shell> cp binaries/Linux/intel/static/vlmcsd-x64-musl-static /usr/bin/vlmcsd
# x86架构
shell> cp binaries/Linux/intel/static/vlmcsd-x86-musl-static /usr/bin/vlmcsd
# arm架构
shell> cp binaries/Linux/arm/little-endian/static/vlmcsd-armv7el-uclibc-static /usr/bin/vlmcsd

# 确认vlmcsd是否正常
shell> vlmcsd -V
···vlmcsd版本信息···
```

将vlmcsd配置为系统服务

```
shell> cp /var/www/kms-server/conf/vlmcsd.service /etc/systemd/system/
```

`vlmcsd.service` 文件内容如下，可按需要修改

```
[Unit]
Description=KMS Server By vlmcsd
After=network.target

[Service]
Type=forking
PIDFile=/var/run/vlmcsd.pid
ExecStart=/usr/bin/vlmcsd -p /var/run/vlmcsd.pid
ExecStop=/bin/kill -HUP $MAINPID
PrivateTmp=true

[Install]
WantedBy=multi-user.target
```

载入systemctl服务

```
shell> systemctl daemon-reload
shell> systemctl enable vlmcsd
shell> systemctl start vlmcsd
shell> systemctl status vlmcsd
···
Active: active (running) ···
···
```

## 容器构建

**本地构建**

```
# 克隆仓库
shell> git clone https://github.com/dnomd343/kms-server.git
shell> cd kms-server
# 构建镜像
shell> docker build -t kms-server .
```

**交叉构建**

```
# 构建并推送至Docker Hub
shell> docker buildx build -t dnomd343/kms-server --platform="linux/amd64,linux/arm64,linux/386,linux/arm/v7" https://github.com/dnomd343/kms-server.git#master --push
```

## 许可证

MIT ©2021 [@dnomd343](https://github.com/dnomd343)