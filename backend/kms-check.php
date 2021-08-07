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
    $cmd .= ' -G temp';
    $raw = shell_exec($cmd); // 执行vlmcs测试
    preg_match_all('/Sending activation request \(KMS V6\)/', $raw, $match);
    if (count($match[0]) == 6) { // KMS服务器连接成功
        return array(
            'status' => 'ok',
            'message' => 'success'
        );
    } else { // KMS服务器连接异常
        return array(
            'status' => 'error',
            'message' => 'connect fail'
        );
    }
}

?>
