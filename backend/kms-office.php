<?php

$office['2010'] = 'if exist "%ProgramFiles%\Microsoft Office\Office14\ospp.vbs" cd /d "%ProgramFiles%\Microsoft Office\Office14"
if exist "%ProgramFiles(x86)%\Microsoft Office\Office14\ospp.vbs" cd /d "%ProgramFiles(x86)%\Microsoft Office\Office14"
cscript ospp.vbs /inpkey:VYBBJ-TRJPB-QFQRF-QFT4D-H3GVB';

$office['2013'] = 'if exist "%ProgramFiles%\Microsoft Office\Office15\ospp.vbs" cd /d "%ProgramFiles%\Microsoft Office\Office15"
if exist "%ProgramFiles(x86)%\Microsoft Office\Office15\ospp.vbs" cd /d "%ProgramFiles(x86)%\Microsoft Office\Office15"
cscript ospp.vbs /inpkey:YC7DK-G2NP3-2QQC3-J6H88-GVGXT';

$office['2016'] = 'if exist "%ProgramFiles%\Microsoft Office\Office16\ospp.vbs" cd /d "%ProgramFiles%\Microsoft Office\Office16"
if exist "%ProgramFiles(x86)%\Microsoft Office\Office16\ospp.vbs" cd /d "%ProgramFiles(x86)%\Microsoft Office\Office16"
cscript ospp.vbs /inpkey:XQNVK-8JYDB-WJ9W3-YJ8YR-WFG99';

$office['2019'] = 'if exist "%ProgramFiles%\Microsoft Office\Office16\ospp.vbs" cd /d "%ProgramFiles%\Microsoft Office\Office16"
if exist "%ProgramFiles(x86)%\Microsoft Office\Office16\ospp.vbs" cd /d "%ProgramFiles(x86)%\Microsoft Office\Office16"
cscript ospp.vbs /inpkey:NMMKJ-6RK4F-KMJVX-8D9MJ-6MWKP';

function loadOsppInfo() { // 初始化ospp信息
    global $webSite;
    global $osppOption, $osppDescription, $osppDescriptionCn;

    $osppOption[] = '/dstatus';
    $osppDescription[] = 'Displays license information for installed product keys.';
    $osppDescriptionCn[] = '显示当前已安装产品密钥的许可证信息';

    $osppOption[] = '/dstatusall';
    $osppDescription[] = 'Displays license information for all installed licenses.';
    $osppDescriptionCn[] = '显示当前已安装的所有许可证信息';

    $osppOption[] = '/unpkey:XXXXX';
    $osppDescription[] = 'Uninstalls an product key with the last five digits of it.';
    $osppDescriptionCn[] = '卸载已安装的产品密钥（最后5位）';

    $osppOption[] = '/inpkey:XXXXX-XXXXX-XXXXX-XXXXX-XXXXX';
    $osppDescription[] = 'Installs a product key with a user-provided product key.';
    $osppDescriptionCn[] = '安装产品密钥';

    $osppOption[] = '/sethst:' . $webSite;
    $osppDescription[] = 'Sets a KMS host name with a user-provided host name.';
    $osppDescriptionCn[] = '设置 KMS 主机名';

    $osppOption[] = '/remhst';
    $osppDescription[] = 'Removes KMS host name and sets port to default.';
    $osppDescriptionCn[] = '删除 KMS 主机名';

    $osppOption[] = '/act';
    $osppDescription[] = 'Activates installed Office product keys.';
    $osppDescriptionCn[] = '激活 Office';
}

function loadOfficeCmd() { // 初始化Office激活命令
    global $webSite, $office;
    $activeCmd = 'cscript ospp.vbs /sethst:' . $webSite . PHP_EOL . 'cscript ospp.vbs /act';
    $activeCmd .= PHP_EOL . 'cscript ospp.vbs /dstatus' . PHP_EOL;
    foreach ($office as $index => $officeKmsCmd) {
        $office[$index] .= PHP_EOL . $activeCmd;
    }
}

function showOfficeHelp() { // 命令行输出Office激活帮助
    loadOsppInfo();
    loadOfficeCmd();
    global $office, $osppOption, $osppDescription, $osppDescriptionCn;
    foreach ($office as $index => $officeKmsCmd) {
        echo str_pad('', 34, ' ') . 'Office Professional Plus ' . $index . ' VL Activation Command' . PHP_EOL;
        echo str_pad('', 120, '-') . PHP_EOL;
        echo $officeKmsCmd;
        echo str_pad('', 120, '-') . PHP_EOL . PHP_EOL;
    }
    $length = 0;
    $length_first = 0;
    foreach ($osppOption as $index => $option) { // 获取最长的字符串长度
        $strLength = strlen($option) + strlen($osppDescription[$index]);
        if ($length < $strLength) {
            $length = $strLength;
            $length_first = strlen($option);
        }
    }
    $title = 'Common activation commands';
    echo str_pad('', floor(($length - strlen($title) + 26) / 2), ' ') . $title . PHP_EOL;
    echo '┏' . str_pad('', $length + 24, '-') . '┓' . PHP_EOL;
    foreach ($osppOption as $index => $option) {
        echo '| cscript ospp.vbs ' . str_pad($osppOption[$index], $length_first, ' ') . ' | ';
        echo str_pad($osppDescription[$index], $length - $length_first + 2, ' ') . ' |' . PHP_EOL;
    }
    echo '┗' . str_pad('', $length + 24, '-') . '┛' . PHP_EOL . PHP_EOL;
    echo 'These commands are only applicable to the VL version of Office.' . PHP_EOL;
    echo 'If it is a Retail version, please convert it to Volume first.' . PHP_EOL . PHP_EOL;
}

function webOfficeHelp() { // 网页输出Office激活帮助
    loadOsppInfo();
    loadOfficeCmd();
    global $office, $osppOption, $osppDescription, $osppDescriptionCn;
    echo '<!DOCTYPE html><html><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0"><title>';
    echo 'Office KMS Server';
    echo '</title><link rel="stylesheet" href="./assets/style.css" /></head><body><div>';
    foreach ($office as $officeVersion => $officeKmsCmd) {
        echo '<h2>Office Professional Plus ' . $officeVersion . ' VL</h2>' . PHP_EOL;
        echo '<pre><code>' . $officeKmsCmd . '</code></pre>' . PHP_EOL;
    }
    echo '<h2>常用激活命令</h2>' . PHP_EOL;
    echo '<table><thead><tr><th>命令</th><th>说明</th></tr></thead><tbody>';
    foreach ($osppOption as $index => $option) {
        echo '<tr><td>cscript ospp.vbs ' . $option . '</td>';
        echo '<td>' . $osppDescriptionCn[$index] . '</td></tr>';
    }
    echo '</tbody></table><br>' . PHP_EOL;
    echo '<p>以上命令仅用于激活VL版本的Office，如果当前为Retail版本，请先转化为批量授权版本。</p>' . PHP_EOL;
    echo '</div></body></html>';
}

?>