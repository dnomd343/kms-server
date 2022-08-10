<?php

function genStr(int $length, string $fillStr = ' '): string { // generate a string of specified length
    return str_pad('', $length, $fillStr);
}

function lenUtf8(string $str): int { // get string length (Chinese -> 2)
    return strlen(iconv('utf-8', 'gb2312', $str));
}

function getKeys(bool $isWinServer = false): array { // get kms keys asset
    $keysAsset = json_decode(file_get_contents('../assets/kms-keys.json'), true);
    return $isWinServer ? array_reverse($keysAsset['win-server']) : $keysAsset['win'];
}

function officeCommand(string $version, string $key, string $host): string {
    $command = 'if exist "%ProgramFiles%\Microsoft Office\\' . $version . '\ospp.vbs" ';
    $command .= 'cd /d "%ProgramFiles%\Microsoft Office\\' . $version . "\"\n";
    $command .= 'if exist "%ProgramFiles(x86)%\Microsoft Office\\' . $version . '\ospp.vbs" ';
    $command .= 'cd /d "%ProgramFiles(x86)%\Microsoft Office\\' . $version . "\"\n";
    $command .= "cscript ospp.vbs /inpkey:$key\n";
    $command .= "cscript ospp.vbs /sethst:$host\n";
    $command .= "cscript ospp.vbs /act\n";
    return $command . "cscript ospp.vbs /dstatus\n";
}

function osppCommand(string $host): array {
    return array(
        '/dstatus' => ['Displays license information for installed product keys.', '显示当前已安装产品密钥的许可证信息'],
        '/dstatusall' => ['Displays license information for all installed licenses.', '显示当前已安装的所有许可证信息'],
        '/unpkey:XXXXX' => ['Uninstalls an product key with the last five digits of it.', '卸载已安装的产品密钥（最后5位）'],
        '/inpkey:XXXXX-XXXXX-XXXXX-XXXXX-XXXXX' => ['Installs a product key with a user-provided product key.', '安装产品密钥'],
        "/sethst:$host" => ['Sets a KMS host name with a user-provided host name.', '设置 KMS 主机名'],
        '/remhst' => ['Removes KMS host name and sets port to default.', '删除 KMS 主机名'],
        '/act' => ['Activates installed Office product keys.', '激活 Office'],
    );
}