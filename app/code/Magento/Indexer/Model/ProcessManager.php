<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model;

use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;

/**
 * Provide functionality for executing user functions in multi-thread mode.
 */
class ProcessManager
{
    /**
     * Threads count environment variable name
     */
    const THREADS_COUNT = 'MAGE_INDEXER_THREADS_COUNT';

    /** @var bool */
    private $failInChildProcess = false;

    /** @var \Magento\Framework\App\ResourceConnection */
    private $resource;

    /** @var \Magento\Framework\Registry */
    private $registry;

    /** @var int|null */
    private $threadsCount;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Registry $registry
     * @param int|null $threadsCount
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Registry $registry = null,
        int $threadsCount = null,
        LoggerInterface $logger = null
    ) {
        $this->resource = $resource;
        if (null === $registry) {
            $registry = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Registry::class
            );
        }
        $this->registry = $registry;
        $this->threadsCount = (int)$threadsCount;
        $this->logger = $logger ?? ObjectManager::getInstance()->get(
            LoggerInterface::class
        );
    }

    /**
     * Execute user functions
     *
     * @param \Traversable $userFunctions
     */
    public function execute($userFunctions)
    {
        if ($this->threadsCount > 1 && $this->isCanBeParalleled() && !$this->isSetupMode() && PHP_SAPI == 'cli') {
            $this->multiThreadsExecute($userFunctions);
        } else {
            $this->simpleThreadExecute($userFunctions);
        }
    }

    /**
     * Execute user functions in singleThreads mode
     *
     * @param \Traversable $userFunctions
     */
    private function simpleThreadExecute($userFunctions)
    {
        foreach ($userFunctions as $userFunction) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            call_user_func($userFunction);
        }
    }

    /**
     * Execute user functions in multiThreads mode
     *
     * @param \Traversable $userFunctions
     * @throws \RuntimeException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function multiThreadsExecute($userFunctions)
    {
        $this->resource->closeConnection(null);
        $threadNumber = 0;
        foreach ($userFunctions as $userFunction) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new \RuntimeException('Unable to fork a new process');
            } elseif ($pid) {
                $this->executeParentProcess($threadNumber);
            } else {
                $this->startChildProcess($userFunction);
            }
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        while (pcntl_waitpid(0, $status) != -1) {
            //Waiting for the completion of child processes
            if ($status > 0) {
                $this->failInChildProcess = true;
            }
        }

        if ($this->failInChildProcess) {
            throw new \RuntimeException('Fail in child process');
        }
    }

    /**
     * Is process can be paralleled
     *
     * @return bool
     */
    private function isCanBeParalleled(): bool
    {
        return function_exists('pcntl_fork');
    }

    /**
     * Is setup mode
     *
     * @return bool
     */
    private function isSetupMode(): bool
    {
        return $this->registry->registry('setup-mode-enabled') ?: false;
    }

    /**
     * Start child process
     *
     * @param callable $userFunction
     */
    private function startChildProcess(callable $userFunction)
    {
        try {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $status = call_user_func($userFunction);
            $status = is_int($status) ? $status : 0;
        } catch (\Throwable $e) {
            $status = 1;
            $this->logger->error(
                __('Child process failed with message: %1', $e->getMessage()),
                ['exception' => $e]
            );
        } finally {
            // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
            exit($status);
        }
    }

    /**
     * Execute parent process
     *
     * @param int $threadNumber
     */
    private function executeParentProcess(int &$threadNumber)
    {
        $threadNumber++;
        if ($threadNumber >= $this->threadsCount) {
            // phpcs:disable Magento2.Functions.DiscouragedFunction
            pcntl_wait($status);
            if (pcntl_wexitstatus($status) !== 0) {
                // phpcs:enable
                $this->failInChildProcess = true;
            }
            $threadNumber--;
        }
    }
}
