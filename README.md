# KMS Server

- [x] 支持Windows与Office全系列激活

- [x] 快速部署，仅需一句命令即可使用

- [x] 内置各版本KMS密钥，无需自行查询

- [x] 网页端、命令行下快速检索密钥与说明

- [x] API接口，检查其他KMS服务器工作状态

## 使用方法

以 `kms.343.re` 为例，在成功部署KMS服务以后，你可以通过[网页](https://kms.343.re/)或命令行环境获取激活密钥。

```
# 输出操作说明
shell> curl kms.343.re

# 输出Windows的KMS密钥
shell> curl kms.343.re/win

# 输出Windows Server的KMS密钥
shell> curl kms.343.re/win-server

# 输出Office激活说明
shell> curl kms.343.re/office

# 测试其他KMS服务器是否正常
shell> curl "kms.343.re/check?host=kms.dnomd343.top&port=1688"
```

在其他需要KMS服务的地方，填入 `kms.343.re` 即可激活。

## 快速部署

### 1. 防火墙检查

> 服务器1688端口接受外网KMS激活请求，务必检查是否被防火墙拦截。

如果开启了 `ufw` 防火墙服务，使用以下命令放行：

```
shell> ufw allow 1688/tcp
shell> ufw status
```

如果开启了 `firewalld` 防火墙服务，使用以下命令放行：

```
shell> firewall-cmd --zone=public --add-port=1688/tcp --permanent
shell> firewall-cmd --reload
shell> firewall-cmd --list-ports
```

部分云服务商可能会在网页端控制台提供防火墙服务，请在对应页面开放 `1688/tcp` 端口。

### 2. Docker环境

```
shell> docker --version
···Docker版本信息···
```

若上述命令出现 `command not found`，使用以下命令安装Docker

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

### 3. 镜像获取

> 在[Docker Hub](https://hub.docker.com/repository/docker/dnomd343/kms-server)或[Github Package](https://github.com/dnomd343/TProxy/pkgs/container/kms-server)可以查看已构建的镜像。

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

### 4. 启动KMS服务

> 下述命令中，容器路径可替换为上述其他源

若只需KMS激活功能，使用以下命令，并忽略后续步骤

```
shell> docker run -d --restart=always --name kms -p 1688:1688 dnomd343/kms-server
```

如需启用其他功能，继续以下步骤：

```
# 映射容器1688与1689端口到宿主机
shell> docker run -d --restart=always --name kms -p 1688-1689:1688-1689 dnomd343/kms-server

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

### 5. 配置反向代理

将用于KMS服务的域名DNS解析到当前服务器，以Nginx为例：

```
# 进入Nginx配置目录
shell> cd /etc/nginx/conf.d

# 下载配置文件，国内用户可替换为以下链接
# https://cdn.jsdelivr.net/gh/dnomd343/kms-server@master/conf/nginx/docker.conf
shell> wget https://github.com/dnomd343/kms-server/raw/master/conf/nginx/docker.conf -O kms.conf

# 修改配置文件中域名、证书、端口等信息
shell> vim kms.conf
```

配置文件如下，按备注修改域名与证书：

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

访问上述域名，正常显示页面即部署成功。

### 6. 检查服务是否正常

以 `kms.dnomd343.top` 为例，使用以下命令检查KMS服务器状态：

```
shell> curl "https://kms.343.re/check?host=kms.dnomd343.top&port=1688"
{"status":"ok","message":"success"}
```

输出中 `status` 字段为 `ok` 即工作正常；若为 `error`，请检查防火墙是否屏蔽1688/tcp端口。

## 常规部署

> 此方式较为繁琐且可能存在版本兼容问题，仅用于不方便安装Docker的情况，不建议使用。

<details>

<summary><b>配置方式</b></summary>

<br/>

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

测试服务是否正常工作

```
shell> curl 127.0.0.1:1689/win
···不同版本Windows的KMS密钥···
```

**4. 配置vlmcsd服务**

在vlmcsd的[Github页面](https://github.com/Wind4/vlmcsd/releases)获取最新release包。

```
# 下载最新release并解压
shell> wget https://github.com/Wind4/vlmcsd/releases/download/svn1113/binaries.tar.gz
shell> tar xf binaries.tar.gz

# 不同架构主机选择不同文件，以下为常见示例
# x86-64架构
shell> cp binaries/Linux/intel/static/vlmcsd-x64-musl-static /usr/bin/vlmcsd
shell> cp binaries/Linux/intel/static/vlmcs-x64-musl-static /usr/bin/vlmcs
# x86架构
shell> cp binaries/Linux/intel/static/vlmcsd-x86-musl-static /usr/bin/vlmcsd
shell> cp binaries/Linux/intel/static/vlmcs-x86-musl-static /usr/bin/vlmcs
# arm架构
shell> cp binaries/Linux/arm/little-endian/static/vlmcsd-armv7el-uclibc-static /usr/bin/vlmcsd
shell> cp binaries/Linux/arm/little-endian/static/vlmcs-armv7el-uclibc-static /usr/bin/vlmcs
```

确认是否正确安装

```
shell> vlmcsd -V
···vlmcsd版本信息···
shell> vlmcs -V
···vlmcs版本信息···
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

</details>

## 开发相关

### JSON接口

`kms-server` 预留了以下JSON接口，用于输出内置的KMS密钥。

+ `/win/json`：输出各版本Windows的KMS密钥；

+ `/win-server/json`：输出各版本Windows Server的KMS密钥；

+ `/json`：输出各版本Windows和Windows Server的KMS密钥；

### KMS测试

`kms-server` 内置了检测其他KMS服务器是否可用的功能，接口位于 `/check` 下，使用时指定目标服务器以下参数

+ `host`：服务器IPv4、IPv6地址或域名

+ `port`：KMS服务端口，默认1688

+ `site`：KMS请求中的 `workstation` 参数，可选

```
shell> curl "kms.343.re/check?host=47.242.30.65"
{"status":"ok","message":"success"}

shell> curl "kms.343.re/check?host=kms.dnomd343.top&port=8861"
{"status":"error","message":"connect fail"}
```

### 容器构建

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
shell> docker buildx build -t dnomd343/kms-server --platform="linux/amd64,linux/arm64,linux/386,linux/arm/v7" https://github.com/dnomd343/kms-server.git --push
```

## 许可证

MIT ©2021 [@dnomd343](https://github.com/dnomd343)
