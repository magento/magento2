<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ParallelProcess\Fork;

/**
 * Fork mechanism.
 */
interface ForkManagerInterface
{
    /**
     * Creates forked process, returns it's ID to main process.
     *
     * @throws ChildProcessException In child process.
     * @throws \Throwable If it's impossible to create a fork.
     *
     * @return int
     */
    public function fork(): int;

    /**
     * Wait for a child process to finish.
     *
     * @param int $forkId
     * @throws \InvalidArgumentException When child with given ID doesn't exist.
     *
     * @return bool True if finished successfully,
     * false if the process exited with an error.
     */
    public function waitForProcessExit(int $forkId): bool;
}
