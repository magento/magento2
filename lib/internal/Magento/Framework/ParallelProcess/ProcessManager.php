<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ParallelProcess;

use Magento\Framework\ParallelProcess\Fork\ChildProcessException;
use Magento\Framework\ParallelProcess\Fork\ExitEventListenerInterface;
use Magento\Framework\ParallelProcess\Fork\ForkManagerInterface;
use Magento\Framework\ParallelProcess\Process\Data;
use Magento\Framework\ParallelProcess\Process\ExitedWithErrorException;
use Magento\Framework\ParallelProcess\Process\RunnerInterface;

/**
 * Manages parallel PHP processes.
 */
class ProcessManager
{
    /**
     * @var RunnerInterface
     */
    private $runner;

    /**
     * @var ForkManagerInterface
     */
    private $fork;

    /**
     * @var int|null
     */
    private $limit;

    /**
     * @var Data[]
     */
    private $processes;

    /**
     * @var string[] Map of Data IDs => pids.
     */
    private $pids = [];

    /**
     * @var Data[]
     */
    private $failed = [];

    /**
     * @var int
     */
    private $forksStarted = 0;

    /**
     * @param RunnerInterface $runner
     * @param ForkManagerInterface $fork
     * @param Data[] $processes
     * @param int|null $limit max number of forked processes.
     */
    public function __construct(
        RunnerInterface $runner,
        ForkManagerInterface $fork,
        array $processes,
        int $limit = null
    ) {
        $this->runner = $runner;
        $this->fork = $fork;
        if ($limit < 0) {
            throw new \InvalidArgumentException();
        }
        $this->limit = $limit;
        foreach ($processes as $process) {
            $this->processes[$process->getId()] = $process;
        }
    }

    /**
     * Run all processes.
     *
     * @throws ExitedWithErrorException When some processes failed.
     */
    public function run()
    {
        //Waiting for fork processes to finish to count them out.
        $handler = function (int $forkId) {
            if (in_array($forkId, $this->pids)) {
                $this->forksStarted--;
            }
        };
        $exitListener = new class($handler)
            implements ExitEventListenerInterface {
            /**
             * @var callable
             */
            private $handler;

            /**
             * @param callable $handler
             */
            public function __construct(callable $handler)
            {
                $this->handler = $handler;
            }

            /**
             * @inheritDoc
             */
            public function observe(int $forkId, bool $successfulRun)
            {
                ($this->handler)($forkId, $successfulRun);
            }
        };
        $this->fork->addExitEventListener($exitListener);

        //Sorting processes based on their dependencies.
        $sorted = array_values($this->processes);
        usort(
            $sorted,
            function (Data $a, Data $b) {
                if (in_array($a->getId(), $b->getDependsOn(), true)) {
                    return -1;
                }
                if (in_array($b->getId(), $a->getDependsOn(), true)) {
                    return 1;
                }

                return 0;
            }
        );
        //Running processes.
        foreach ($sorted as $process) {
            $this->startProcess($process);
        }
        //Collecting processes' results.
        $remainingProcesses = array_keys($this->pids);
        foreach ($remainingProcesses as $dataId) {
            $this->waitForProcessExit($dataId);
        }
        //Deleting links for this manager.
        $this->fork->remoteExitEventListener($exitListener);
        unset($exitListener);
        //Notifying if there were failed processes.
        if ($this->failed) {
            throw new ExitedWithErrorException($this->failed);
        }
    }

    /**
     * @return int
     * @throws \Throwable
     */
    private function fork(): int
    {
        if ($this->limit === 1) {
            throw new \RuntimeException('Limited to only 1 process');
        }

        return $this->fork->fork();
    }

    /**
     * @param Data $process
     * @return void
     */
    private function startProcess(Data $process)
    {
        //Dealing with provider processes.
        /** @var bool $dependencyFailed One of provider processes failed */
        $dependencyFailed = false;
        foreach ($process->getDependsOn() as $providerId) {
            if (array_key_exists($providerId, $this->pids)) {
                if (!$this->waitForProcessExit($providerId)) {
                    $dependencyFailed = true;
                }
            }
        }
        if ($dependencyFailed) {
            $this->failed[] = $process;
            return;
        }

        //Launching process.
        try {
            //Main process.
            $this->pids[$process->getId()] = $this->fork();
        } catch (ChildProcessException $exception) {
            //Child process.
            $this->runChildProcess($process);
        } catch (\Throwable $exception) {
            //Not using forks.
            $this->runLocalProcess($process);
        }
    }

    /**
     * Wait for a process to exit and record results.
     *
     * @param string $dataId
     *
     * @return bool
     */
    private function waitForProcessExit(string $dataId): bool
    {
        $result = $this->fork->waitForProcessExit($this->pids[$dataId]);
        if (!$result) {
            $this->failed[] = $this->processes[$dataId];
        }
        unset($this->pids[$dataId]);

        return $result;
    }

    /**
     * @param Data $process
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    private function runChildProcess(Data $process)
    {
        $code = 0;
        try {
            $this->runner->run($process->getData());
        } catch (\Throwable $ex) {
            $code = $ex->getCode() ?: 1;
        }

        exit($code);
    }

    /**
     * @param Data $process
     *
     * @return void
     */
    private function runLocalProcess(Data $process)
    {
        try {
            $this->runner->run($process->getData());
        } catch (\Throwable $ex) {
            $this->failed[] = $process;
        }
    }
}
