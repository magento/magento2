<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ParallelProcess;

use Magento\Framework\ParallelProcess\Fork\ChildProcessException;
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
        //Dividing processes to honor limit.
        if ($this->limit > 1) {
            $chunkSize = ceil(count($sorted) / $this->limit);
        } else {
            $chunkSize = 1;
        }
        /** @var Data[][] $processChunks */
        $processChunks = array_chunk($sorted, $chunkSize, true);
        //Running processes.
        foreach ($processChunks as $processes) {
            $this->startProcesses($processes);
        }
        //Collecting processes' results.
        $remainingProcesses = array_keys($this->pids);
        foreach ($remainingProcesses as $dataId) {
            $this->waitForProcessExit($dataId);
        }
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
     * @param Data[] $processes
     * @return void
     */
    private function startProcesses(array $processes)
    {
        $processesToLaunch = [];
        //Dealing with providing processes.
        foreach ($processes as $process) {
            foreach ($process->getDependsOn() as $providerId) {
                //If we know that a provider already failed then
                //not launching the process at all.
                if (in_array(
                    $this->processes[$providerId],
                    $this->failed,
                    true
                )) {
                    $this->failed[] = $process;
                    continue 2;
                }
                //If a provider was unsuccessful then not launching the
                //process at all.
                if (array_key_exists($providerId, $this->pids)) {
                    if (!$this->waitForProcessExit($providerId)) {
                        $this->failed[] = $process;
                        continue 2;
                    }
                }
            }
            $processesToLaunch[] = $process;
        }
        if (!$processesToLaunch) {
            return;
        }

        //Launching processes.
        try {
            //Main process.
            $forkId = $this->fork();
            foreach ($processesToLaunch as $process) {
                $this->pids[$process->getId()] = $forkId;
            }
        } catch (ChildProcessException $exception) {
            //Child processes.
            $this->runChildProcesses($processesToLaunch);
        } catch (\Throwable $exception) {
            //Not using fork.
            $this->runLocalProcesses($processesToLaunch);
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
     * @param Data[] $processes
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    private function runChildProcesses(array $processes)
    {
        $code = 0;
        try {
            foreach ($processes as $process) {
                $this->runner->run($process->getData());
            }
        } catch (\Throwable $ex) {
            $code = $ex->getCode() ?: 1;
        }

        exit($code);
    }

    /**
     * @param Data[] $processes
     *
     * @return void
     */
    private function runLocalProcesses(array $processes)
    {
        try {
            foreach ($processes as $process) {
                $this->runner->run($process->getData());
            }
        } catch (\Throwable $ex) {
            $this->failed = array_merge($this->failed, $processes);
        }
    }
}
