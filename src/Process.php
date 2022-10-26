<?php

class Process {
    private $process;
    public int $pid = -1;
    public mixed $stdin = null;
    public mixed $stdout = null; // only capture enabled
    public mixed $stderr = null; // only capture enabled

    public function __construct(array $command, bool $capture = false, array $env = array()) {
        $desc = array(
            0 => array('pipe', 'r') // stdin
        );
        if ($capture) {
            $desc[1] = array('pipe', 'w'); // stdout
            $desc[2] = array('pipe', 'w'); // stderr
        }
        logging::debug('Process command -> `' . implode(' ', $command) . '`');
        $this->process = proc_open($command, $desc, $pipes, null, $env); // start process
        if (is_resource($this->process)) { // process start success
            $this->pid = proc_get_status($this->process)['pid'];
            $this->stdin = $pipes[0];
            if ($capture) { // add capture pipes
                $this->stdout = $pipes[1];
                $this->stderr = $pipes[2];
            }
        }
    }

    private function getCapture(mixed $stream): string { // read data from target stream
        return ($stream == null) ? '' : stream_get_contents($stream);
    }

    public function getStdout(): string { // fetch data from stdout pipe
        return $this->getCapture($this->stdout);
    }

    public function getStderr(): string { // fetch data from stderr pipe
        return $this->getCapture($this->stderr);
    }

    public function signal(int $signal): void { // send signal to sub process
        proc_terminate($this->process, $signal);
    }

    public function status(): array { // get process status
        return proc_get_status($this->process);
    }

    public function isAlive(): bool { // whether sub process still running
        return $this->status()['running'];
    }

    public function quit(): int {
        fclose($this->stdout); // close pipe before proc_close (prevent deadlock)
        fclose($this->stderr);
        return proc_close($this->process); // return status code
    }
}
