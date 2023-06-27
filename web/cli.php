<?php

require_once "utils.php";

/// Print list structural under command line.
///
///    Example
/// ┏-----------┓
/// | xxxx      |
/// | xxxxxxxxx |
/// | xxxxxx    |
/// ┗-----------┛
function showCliList(string $title, array $content): void {
    $contentLen = 0;
    foreach ($content as $row) { // found the longest row
        $contentLen = ($contentLen < stringLen($row)) ? stringLen($row) : $contentLen;
    }

    $bodyLen = $contentLen + 4; // add 4-slots -> `| xxx |`
    $titleLen = stringLen($title);
    $bodyOffset = $titleOffset = 0;
    if ($titleLen > $bodyLen) { // title longer than body
        $bodyOffset = floor(($titleLen - $bodyLen) / 2); // body move right
    } else {
        $titleOffset = floor(($bodyLen - $titleLen) / 2); // title move right
    }

    printf("%s%s\n", stringGen($titleOffset), $title); // show list title
    printf("%s┏-%s-┓\n", stringGen($bodyOffset), stringGen($contentLen, '-'));
    foreach ($content as $row) { // show list body
        printf("%s| %s%s |\n",
            stringGen($bodyOffset), $row, stringGen($contentLen - stringLen($row))
        );
    }
    printf("%s┗-%s-┛\n", stringGen($bodyOffset), stringGen($contentLen, '-'));
}

/// Print table with two columns under command line.
///
///      Example
/// ┏---------------┓
/// | xxx  | xxxxxx |
/// | xxxx | xxxxxx |
/// | xx   | xxxxxx |
/// ┗---------------┛
function showCliTable(string $title, array $content): void {
    $length = 0;
    foreach ($content as $row => $_) { // found the longest item
        $length = ($length < stringLen($row)) ? stringLen($row) : $length;
    }
    $cache = array();
    foreach ($content as $row_1 => $row_2) {
        $cache[] = $row_1 . stringGen($length - stringLen($row_1)) . ' | ' . $row_2;
    }
    showCliList($title, $cache); // render as list
}

class CliOutput {
    /// Print help message under command line.
    public function showHelp(string $host, string $port): void {
        if (isIPv6($host)) {
            $host = "[$host]"; // add ipv6 bracket
        }
        $kmsServer = $host;
        $urlPrefix = "http://$host";
        if ($port != 1688) { // not default port
            $kmsServer = "$kmsServer:$port"; // add service port
        }

        echo PHP_EOL;
        showCliList('Windows Activation', [
            'slmgr /upk',
            'slmgr /ipk KMS_KEY',
            "slmgr /skms $kmsServer",
            'slmgr /ato',
            'slmgr /dlv',
        ]);
        echo "\nKMS_KEY\n";
        echo "  -> $urlPrefix/win\n";
        echo "  -> $urlPrefix/win-server\n";
        echo "\nOffice\n";
        echo "  -> $urlPrefix/office\n\n";
    }

    /// Print GVLKs under command line.
    public function showGvlks(bool $isWinServer): void {
        echo PHP_EOL;
        foreach (loadGvlks($isWinServer) as $version => $content) {
            showCliTable($version, $content);
            echo PHP_EOL;
        }
    }

}

$cli = new CliOutput();
//$cli->showHelp('kms.343.re', 1688);
//$cli->showHelp('kms.343.re', 1689);
//$cli->showHelp('1.1.1.1', 1689);
//$cli->showHelp('fc00::', 1689);

$cli->showGvlks(false);
$cli->showGvlks(true);
