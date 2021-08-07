<?php

// TODO: site/app

function checkKms($config) { // 检测KMS服务器是否可用
    if (isset($config['host'])) {
        $host = $config['host'];
    } else {
        return json_encode(array(
            'status' => 'error',
            'message' => 'host param not exist'
        ));
    }
    if (isset($config['port'])) {
        $port = $config['port'];
    } else {
        $port = 1688;
    }
    if (isset($config['version'])) {
        $version = $config['version'];
    } else {
        $version = 6;
    }

    $cmd = 'vlmcs ';
    if (isDomain($host) || isIPv4($host)) {
        $cmd .= $host;
    } else if (isIPv6($host)) {
        $cmd .= '[' . $host . ']';
    } else {
        return array(
            'status' => 'error',
            'message' => 'illegal host'
        );
    }
    if ($port > 65535 || $port < 0) {
        return array(
            'status' => 'error',
            'message' => 'illegal port'
        );
    } else {
        $cmd .= ':' . $port;
    }
    if ($version != 4 && $version != 5 && $version != 6) {
        return array(
            'status' => 'error',
            'message' => 'illegal version'
        );
    } else {
        $cmd .= ' -' . $version;
    }

    $raw = shell_exec($cmd);
    preg_match('/successful/', $raw, $match);
    header('Content-Type: application/json; charset=utf-8');
    if (!count($match)) {
        return array(
            'status' => 'error',
            'message' => 'connect fail'
        );
    } else {
        return array(
            'status' => 'ok',
            'message' => 'success'
        );
    }
}

?>
