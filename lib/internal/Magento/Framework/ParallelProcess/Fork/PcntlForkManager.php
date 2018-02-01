<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ParallelProcess\Fork;

/**
 * Creating forks with pcntl extension.
 */
class PcntlForkManager implements ForkManagerInterface
{
    /**
     * @var int[]
     */
    private $pidsLaunched = [];

    /**
     * @var int[]
     */
    private $pidStatuses = [];

    /**
     * @inheritDoc
     */
    public function fork(): int
    {
        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new \RuntimeException('Couldn\'t fork a process');
        }
        if ($pid === 0) {
            throw new ChildProcessException();
        }
        $this->pidsLaunched[] = $pid;

        return $pid;
    }

    /**
     * @inheritDoc
     */
    public function waitForProcessExit(int $forkId): bool
    {
        if (!in_array($forkId, $this->pidsLaunched, true)) {
            throw new \InvalidArgumentException();
        }

        $result = pcntl_waitpid($forkId, $status);
        if ($result === -1 && !array_key_exists($forkId, $this->pidStatuses)) {
            throw new \RuntimeException('Error on waiting on a child process');
        }
        if (array_key_exists($forkId, $this->pidStatuses)) {
            $status = $this->pidStatuses[$forkId];
        } else {
            $this->pidStatuses[$forkId] = $status;
        }

        if (pcntl_wifexited($status)) {
            $code = pcntl_wexitstatus($status);
            if ($code === 0) {
                return true;
            }
        }

        return false;
    }
}
