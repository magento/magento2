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

    /**
     * Add an event listener for when a fork process finishes.
     *
     * @param ExitEventListenerInterface $eventListener
     * @param int[]|null $forkIds Call observer only for given processes.
     *
     * @return void
     */
    public function addExitEventListener(
        ExitEventListenerInterface $eventListener,
        array $forkIds = null
    );

    /**
     * Remove listener.
     *
     * @param ExitEventListenerInterface $eventListener
     * @throws \InvalidArgumentException When such listener wasn't attached.
     *
     * @return void
     */
    public function remoteExitEventListener(
        ExitEventListenerInterface $eventListener
    );
}
