<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model;

class ProcessManager
{
    /** @var Process[] */
    private $processes = [];

    /**
     * Forks the currently running process.
     *
     * @param callable $callable
     *
     * @return Process
     * @throws \RuntimeException
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function fork(callable $callable)
    {
        $process = $this->createProcess($callable);
        $pid = pcntl_fork();

        if ($pid === -1) {
            throw new \RuntimeException('Unable to fork a new process');
        }

        if ($pid) {
            $process->setPid($pid);
            $this->processes[$pid] = $process;
            return $process;
        }

        // process child process
        $this->processes = [];
        $process->setPid(getmypid());
        $process->run();

        exit(0);
    }

    /**
     * @return Process[]
     */
    public function getProcesses()
    {
        return $this->processes;
    }

    /**
     * @param Process $process
     * @return void
     */
    public function delete(Process $process)
    {
        unset($this->processes[$process->getPid()]);
    }

    /**
     * @param callable $callable
     * @return Process
     */
    private function createProcess(callable $callable)
    {
        return new Process($callable);
    }

    /**
     * Protect against zombie process
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function freeResources()
    {
        foreach ($this->processes as $process) {
            if (pcntl_waitpid($process->getPid(), $status) === -1) {
                throw new \RuntimeException('Error while waiting for process '. $process->getPid());
            }
        }
    }

    /**
     * Free resources
     */
    public function __destruct()
    {
        $this->freeResources();
    }
}
