<?php

function checkKms($config) { // 检测KMS服务器是否可用
    $tempPath = '/var/www/kms-server/backend/';
    if (isset($config['host'])) {
        $host = $config['host'];
    } else { // host参数必选
        return array(
            'status' => 'error',
            'message' => 'host param not exist'
        );
    }
    if (isset($config['port'])) {
        $port = $config['port'];
    } else {
        $port = 1688; // 默认KMS端口
    }
    if (isset($config['site']) && $config['site'] !== '') {
        $site = $config['site'];
    } else {
        $site = null; // site参数可选
    }

    $cmd = 'vlmcs ';
    if (isDomain($host) || isIPv4($host)) {
        $cmd .= $host;
    } else if (isIPv6($host)) {
        $cmd .= '[' . $host . ']'; // IPv6地址需用中括号包围
    } else {
        return array( // host内容不是 IPv4/IPv6/Domain
            'status' => 'error',
            'message' => 'illegal host'
        );
    }
    if ($port > 65535 || $port < 0) { // 端口不存在
        return array(
            'status' => 'error',
            'message' => 'illegal port'
        );
    } else {
        $cmd .= ':' . $port;
    }
    if ($site !== null) {
        $cmd .= ' -w ' . $site; // 加入site参数
    }
    $fileName = md5(rand()) . '.txt'; // 生成随机文件名
    $cmd .= ' -G ' . $tempPath . $fileName;
    shell_exec($cmd); // 执行vlmcs测试
    $raw = shell_exec('ls ' . $tempPath);
    preg_match('/' . $fileName . '/', $raw, $match); // 判断随机文件是否存在
    header('Content-Type: application/json; charset=utf-8');
    if (!count($match)) { // 随机文件不存在 -> KMS连接错误
        return array(
            'status' => 'error',
            'message' => 'connect fail'
        );
    } else { // 随机文件存在 -> KMS测试成功
        shell_exec('rm -f ' . $tempPath . $fileName); // 删除随机文件
        return array(
            'status' => 'ok',
            'message' => 'success'
        );
    }
}

?>
