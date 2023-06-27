<?php

enum VlmcsResult {
    case OK; // target server works fine
    case NOT_KMS; // target not a kms server
    case UNREACHED; // target not reachable
    case ERROR; // something error
}

/**
 * Run a program and capture its stdout.
 *
 * @throws Exception
 */
function runProcess(array $command): string {
    $desc = array(
        0 => ['pipe', 'r'], // stdin
        1 => ['pipe', 'w'], // stdout
        2 => ['pipe', 'w'], // stderr
    );
    $process = proc_open($command, $desc, $pipes, null, array()); // start process
    if (!is_resource($process)) {
        throw new Exception('process running failed');
    }
    while(proc_get_status($process)['running']) { // wait process exit
        usleep(50); // delay 50ms
    }
    return stream_get_contents($pipes[1]); // fetch stdout
}

function vlmcsCheck(string $host, int $port): VlmcsResult {
    $host = str_contains($host, ':') ? "[$host]" : $host; // ipv6 host add bracket
    try {
        $content = runProcess(['vlmcs', $host . ':' . $port]);
    } catch (Exception) {
        return VlmcsResult::ERROR;
    }

    preg_match_all('/Sending activation request \(KMS V6\)/', $content, $match);
    if (count($match[0]) != 0) {
        return VlmcsResult::OK; // kms server works fine
    }
    preg_match_all('/Connecting to .* successful/', $content, $match);
    if (count($match[0]) != 0) {
        return VlmcsResult::NOT_KMS; // server connected but not working
    } else {
        return VlmcsResult::UNREACHED; // kms server connect failed
    }
}

//$t = vlmcsCheck('1.1.1.1', 1688);
//$t = vlmcsCheck('kms.343.re', 1688);
$t = vlmcsCheck('8.210.148.24', 1688);
$t = vlmcsCheck('8.210.148.24', 1689);
//$t = vlmcsCheck('0.0.0.0.', 1688);
//$t = vlmcsCheck('baidu.com', 1688);
var_dump($t);
