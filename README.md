# KMS Server

+ 支持Windows与Office全系列激活

+ 快速部署，仅需一句命令即可使用

+ 内置各版本KMS密钥，无需自行查询

+ 网页端、命令行下快速检索密钥与说明

+ API接口，检查其他KMS服务器工作状态

## 使用方法

以 `kms.343.re` 为例，在成功部署以后：

+ 在需要KMS服务的地方，填入 `kms.343.re` 即可激活；

+ 可通过[网页](https://kms.343.re/)或命令行curl获取激活密钥：

```
# 输出操作说明
shell> curl kms.343.re

# 输出Windows的KMS密钥
shell> curl kms.343.re/win

# 输出Windows Server的KMS密钥
shell> curl kms.343.re/win-server

# 输出Office激活命令
shell> curl kms.343.re/office
```

+ 可用于测试其他KMS服务器是否正常：

```
# 端口号可不填，默认为1688
shell> curl kms.343.re/check/kms.dnomd343.top:1688
KMS Server: kms.dnomd343.top (1688) -> available
```

## 快速部署

### 1. 防火墙检查

> 服务器tcp/1688端口接受外网KMS激活请求，务必检查是否被防火墙拦截。

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

若上述命令未输出版本信息，使用以下命令安装Docker：

```
shell> sudo curl https://get.docker.com | bash
···
···
shell> docker --version
Docker version ···, build ···
```

### 3. 启动KMS服务

> 本项目基于Docker构建，在[Docker Hub](https://hub.docker.com/r/dnomd343/kms-server)或[Github Package](https://github.com/dnomd343/kms-server/pkgs/container/kms-server)可以查看已构建的各版本镜像。

> 容器使用 `1688/tcp` 与 `1689/tcp` 端口，前者用于KMS激活，后者为HTTP接口。

`kms-server` 同时发布在多个镜像源上（国内网络可首选阿里云仓库）：

+ `Docker Hub` ：`dnomd343/kms-server`

+ `Github Package` ：`ghcr.io/dnomd343/kms-server`

+ `阿里云镜像` ：`registry.cn-shenzhen.aliyuncs.com/dnomd343/kms-server`

> 下述命令中，容器路径可替换为上述其他源

若仅需KMS激活功能，使用以下命令，并忽略后续步骤：

```
shell> docker run -d --restart=always --name kms -p 1688:1688 dnomd343/kms-server
```

如需使用其他功能，执行以下命令并继续后面步骤：

```
shell> docker run -d --restart=always --name kms -p 1688-1689:1688-1689 dnomd343/kms-server
```

### 5. 配置反向代理

将用于KMS服务的域名解析到当前服务器，配置反向代理到本机 `1689/tcp` 端口，下面以Nginx为例：

```
# 进入nginx配置目录
shell> cd /etc/nginx/conf.d

# 添加反向代理配置
shell> vim kms.conf
```

配置文件如下，按备注修改域名与证书：

```
server {
    listen 80;
    listen [::]:80;
    server_name kms.343.re;  # 改为自己的KMS域名
    location / {
        if ($http_user_agent !~* (curl|wget)) {  # 来自非命令行的请求，重定向到https
            return 301 https://$server_name$request_uri;
        }
        proxy_set_header Host $http_host;  # 反向代理转发当前域名
        proxy_pass http://127.0.0.1:1689;
    }
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name kms.343.re;  # 改为自己的KMS域名
    ssl_certificate /etc/ssl/certs/343.re/fullchain.pem;  # 改为自己的TLS证书文件
    ssl_certificate_key /etc/ssl/certs/343.re/privkey.pem;  # 改为自己的TLS私钥文件
    
    gzip on;  # 开启gzip压缩，提高加载速度
    gzip_buffers 32 4K;
    gzip_comp_level 6;
    gzip_min_length 100;
    gzip_types application/javascript text/css text/xml;
    gzip_disable "MSIE [1-6]\.";
    gzip_vary on;

    location / {
        proxy_set_header Host $http_host;  # 反向代理转发当前域名
        proxy_pass http://127.0.0.1:1689;
    }
}
```

重启Nginx服务

```
shell> nginx -s reload
```

访问KMS服务域名，页面正常显示即反向代理成功。

### 6. 检查服务是否正常

使用以下命令检查部署的KMS服务器是否正常：

```
# 检查服务器kms.dnomd343.top
shell> curl kms.343.re/check/kms.dnomd343.top
KMS Server: kms.dnomd343.top (1688) -> available
```

输出 `available` 即工作正常，若失败请检查防火墙是否屏蔽1688端口。

## 开发相关

### 运行参数

+ `--debug` ：进入DEBUG模式，输出调试日志

+ `--port` ：指定KMS激活端口，默认值为 `1688`

### JSON接口

`kms-server` 预留了以下JSON接口，用于输出内置的KMS密钥。

+ `/json`：输出全部KMS密钥；

+ `/win/json`：输出各版本Windows的KMS密钥；

+ `/win-server/json`：输出各版本Windows Server的KMS密钥；

### KMS测试

`kms-server` 内置了检测其他KMS服务器是否可用的功能，接口位于 `/check` 下，使用以下参数：

+ `host`：目标服务器IPv4、IPv6地址或域名

+ `port`：目标服务端口，可选，默认为1688

```
shell> curl -sL "https://kms.343.re/check?host=8.210.148.24" | jq .
{
  "success": true,
  "available": true,
  "host": "8.210.148.24",
  "port": 1688,
  "message": "kms server available"
}

shell> curl -sL "https://kms.343.re/check?host=kms.dnomd343.top&port=8861" | jq .
{
  "success": true,
  "available": false,
  "host": "kms.dnomd343.top",
  "port": 8861,
  "message": "kms server connect failed"
}
```

### 容器构建

**本地构建**

```
shell> git clone https://github.com/dnomd343/kms-server.git
shell> cd ./kms-server/
shell> docker build -t kms-server .
```

**交叉构建**

```
shell> docker buildx build -t dnomd343/kms-server --platform="linux/amd64,linux/arm64,linux/386,linux/arm/v7" https://github.com/dnomd343/kms-server.git --push
```

## 许可证

MIT ©2021 [@dnomd343](https://github.com/dnomd343)
