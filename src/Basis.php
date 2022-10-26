<?php

function mimeJson(): void { // return json mime type
    header('Content-Type: application/json; charset=utf-8');
}

function genStr(int $length, string $fillStr = ' '): string { // generate a string of specified length
    return str_pad('', $length, $fillStr);
}

function lenUtf8(string $str): int { // get string length (Chinese -> 2)
    return strlen(iconv('utf-8', 'gb2312', $str));
}

function isIPv4(string $ip): bool {
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
}

function isIPv6(string $ip): bool {
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
}

function isDomain(string $domain): bool {
    $regex = '/^(?=^.{3,255}$)[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+$/';
    preg_match($regex, $domain, $match);
    return count($match) != 0;
}

function isHost(string $host): bool { // IPv4 / IPv6 / Domain
    return isIPv4($host) or isIPv6($host) or isDomain($host);
}

function isPort(int $port): bool {
    return ($port < 65536 and $port > 0);
}

function getKeys(bool $isWinServer = false): array { // get kms keys asset
    $keysAsset = json_decode(file_get_contents('../assets/kms-keys.json'), true);
    return $isWinServer ? array_reverse($keysAsset['win-server']) : $keysAsset['win'];
}

function v6DelBracket(string $host): string {
    if (str_starts_with($host, '[') and str_ends_with($host, ']')) {
        return substr($host, 1, strlen($host) - 2); // remove bracket of ipv6
    }
    return $host; // no change
}

function getHost(): string {
    if (!isset($_SERVER['HTTP_HOST'])) { // missing http host
        return 'KMS_HOST';
    }
    $host = v6DelBracket($_SERVER['HTTP_HOST']); // try to remove ipv6 bracket
    if (isHost($host)) { // valid host
        return $host;
    }
    preg_match_all('/(\S+):\d+$/', $host, $match);
    if (count($match[1]) == 0) { // ${HOST}:${PORT} format not found
        return 'KMS_HOST';
    }
    $host = v6DelBracket($match[1][0]); // try to remove ipv6 bracket again
    $host = ($host == '127.0.0.1' or $host == '::1') ? 'KMS_HOST' : $host; // ignore localhost forward
    return (isHost($host)) ? $host : 'KMS_HOST';
}

function getPort(): int {
    if (getenv('KMS_PORT') == null) {
        return 1688; // default server port
    }
    return intval(getenv('KMS_PORT'));
}

function officeInfo(): array { // office dir and kms key for different version
    return array(
        '2010' => ['Office14', 'VYBBJ-TRJPB-QFQRF-QFT4D-H3GVB'],
        '2013' => ['Office15', 'YC7DK-G2NP3-2QQC3-J6H88-GVGXT'],
        '2016' => ['Office16', 'XQNVK-8JYDB-WJ9W3-YJ8YR-WFG99'],
        '2019' => ['Office16', 'NMMKJ-6RK4F-KMJVX-8D9MJ-6MWKP'],
    );
}

function officeCommand(string $dir, string $key, string $host, int $port): string { // load office active command
    $command = 'if exist "%ProgramFiles%\Microsoft Office\\' . $dir . '\ospp.vbs" ';
    $command .= 'cd /d "%ProgramFiles%\Microsoft Office\\' . $dir . "\"\n";
    $command .= 'if exist "%ProgramFiles(x86)%\Microsoft Office\\' . $dir . '\ospp.vbs" ';
    $command .= 'cd /d "%ProgramFiles(x86)%\Microsoft Office\\' . $dir . "\"\n";
    $command .= "cscript ospp.vbs /inpkey:$key\n";
    $command .= "cscript ospp.vbs /sethst:$host\n";
    if ($port != 1688) {
        $command .= "cscript ospp.vbs /setprt:$port\n";
    }
    $command .= "cscript ospp.vbs /act\n";
    return $command . "cscript ospp.vbs /dstatus\n";
}

function osppCommand(string $host, int $port): array { // load office ospp command
    $osppCmd = array(
        '/dstatus' => ['Display license information for installed product keys.', '显示当前已安装产品密钥的许可证信息'],
        '/dstatusall' => ['Display license information for installed licenses.', '显示当前已安装的所有许可证信息'],
        '/unpkey:XXXXX' => ['Uninstall a product key with the last five digits of it.', '卸载已安装的产品密钥（最后5位）'],
        '/inpkey:XXXXX-XXXXX-XXXXX-XXXXX-XXXXX' => ['Install a product key with user-provided product key.', '安装产品密钥'],
        "/sethst:$host" => ['Set a KMS host name with user-provided host name.', '设置 KMS 主机名'],
        "/setprt:$port" => ['Set a KMS port with user-provided port number.', '设置 KMS 主机端口'],
        '/remhst' => ['Remove KMS host name and sets port to default.', '删除 KMS 主机名'],
        '/act' => ['Activate installedOffice product keys.', '激活 Office'],
    );
    if ($port == 1688) {
        unset($osppCmd["/setprt:$port"]); // remove setprt option with default port
    }
    return $osppCmd;
}
