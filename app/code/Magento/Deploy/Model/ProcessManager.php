<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model;

class ProcessManager
{
    /** @var Process[] */
    private $processes = [];

    /**
     * @var ProcessFactory
     */
    private $processFactory;

    /**
     * ProcessManager constructor.
     * @param ProcessFactory $processFactory
     */
    public function __construct(ProcessFactory $processFactory)
    {
        $this->processFactory = $processFactory;
    }

    /**
     * Forks the currently running process.
     *
     * @param callable $handler
     *
     * @return Process
     * @throws \RuntimeException
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function fork(callable $handler)
    {
        $process = $this->createProcess($handler);
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
     * @param callable $handler
     * @return Process
     */
    private function createProcess(callable $handler)
    {
        return $this->processFactory->create(['handler' => $handler]);
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
