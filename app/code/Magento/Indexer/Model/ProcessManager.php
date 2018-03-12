<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

/**
 * Provide functionality for executing user functions in multi-thread mode.
 */
class ProcessManager
{
    /** @var bool */
    private $failInChildProcess = false;

    /** @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb */
    private $resource;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->resource = $resource;
    }

    /**
     * Execute user functions
     *
     * @param \Traversable $userFunctions
     * @param int $threadsCount
     */
    public function execute($userFunctions, $threadsCount)
    {
        if ($threadsCount > 1 && $this->isCanBeParalleled()) {
            $this->multiThreadsExecute($userFunctions, $threadsCount);
        } else {
            $this->simpleThreadExecute($userFunctions);
        }
    }

    /**
     * Execute user functions in in singleThreads mode
     *
     * @param \Traversable $userFunctions
     */
    private function simpleThreadExecute($userFunctions)
    {
        foreach ($userFunctions as $userFunction) {
            call_user_func($userFunction);
        }
    }

    /**
     * Execute user functions in in multiThreads mode
     *
     * @param \Traversable $userFunctions
     * @param int $threadsCount
     */
    private function multiThreadsExecute($userFunctions, $threadsCount)
    {
        $this->resource->getConnection()->closeConnection();
        $threadNumber = 0;
        foreach ($userFunctions as $userFunction) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new \RuntimeException('Unable to fork a new process');
            } elseif ($pid) {
                $this->executeParentProcess($threadNumber, $threadsCount);
            } else {
                $this->startChildProcess($userFunction);
            }
        }
        while(pcntl_waitpid(0, $status) != -1);

        if ($this->failInChildProcess) {
            throw new \RuntimeException('Fail in child process');
        }

    }

    /**
     * Is process can be paralleled
     *
     * @return bool
     */
    private function isCanBeParalleled()
    {
        return function_exists('pcntl_fork');
    }

    /**
     * Start child process
     *
     * @param callable $userFunction
     */
    private function startChildProcess($userFunction)
    {
        $status = call_user_func($userFunction);
        $status = is_integer($status) ? $status : 0;
        exit($status);
    }

    /**
     * Execute parent process
     *
     * @param int $threadNumber
     * @param int $threadsCount
     */
    private function executeParentProcess(&$threadNumber, $threadsCount)
    {
        $threadNumber++;
        if ($threadNumber >= $threadsCount) {
            pcntl_wait($status);
            if (pcntl_wexitstatus($status) !== 0) {
                $this->failInChildProcess = true;
            }
            $threadNumber--;
        }
    }
}
