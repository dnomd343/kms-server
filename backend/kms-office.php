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

$osppOption[] = '/dstatus';
$osppDescription[] = 'Displays license information for installed product keys.';
$osppDescriptionCn[] = '显示当前已安装产品密钥的许可证信息';

$osppOption[] = '/dstatusall';
$osppDescription[] = 'Displays license information for all installed licenses.';
$osppDescriptionCn[] = '显示当前已安装的所有许可证信息';

$osppOption[] = '/unpkey:XXXXX';
$osppDescription[] = 'Uninstalls an product key with the last five digits of it.';
$osppDescriptionCn[] = '卸载已安装的产品密钥，取密钥的最后 5 位数';

$osppOption[] = '/inpkey:XXXXX-XXXXX-XXXXX-XXXXX-XXXXX';
$osppDescription[] = 'Installs a product key with a user-provided product key.';
$osppDescriptionCn[] = '安装产品密钥';

$osppOption[] = '/sethst:kms.XXX.XX';
$osppDescription[] = 'Sets a KMS host name with a user-provided host name.';
$osppDescriptionCn[] = '设置 KMS 主机名';

$osppOption[] = '/remhst';
$osppDescription[] = 'Removes KMS host name and sets port to default.';
$osppDescriptionCn[] = '删除 KMS 主机名';

$osppOption[] = '/act';
$osppDescription[] = 'Activates installed Office product keys.';
$osppDescriptionCn[] = '激活 Office';

function loadOfficeCmd() {
    global $webSite, $office;
    $activeCmd = 'cscript ospp.vbs /sethst:' . $webSite . PHP_EOL . 'cscript ospp.vbs /act';
    $activeCmd .= PHP_EOL . 'cscript ospp.vbs /dstatus' . PHP_EOL;
    foreach ($office as $index => $officeKmsCmd) {
        $office[$index] .= PHP_EOL . $activeCmd;
    }
}

function showOfficeHelp() {
    loadOfficeCmd();
    global $office;
    $index = '2010';
    foreach ($office as $index => $officeKmsCmd) {
        echo str_pad('', 34, ' ') . 'Office Professional Plus ' . $index . ' VL Activation Command' . PHP_EOL;
        echo str_pad('', 120, '-') . PHP_EOL;
        echo $officeKmsCmd;
        echo str_pad('', 120, '-') . PHP_EOL . PHP_EOL;
    }
    // TODO: Warning of VL version
    //       Usage of ospp.vbs
}

function webOfficeHelp() {
    // TODO: Web of Office KMS Activation
}

?>