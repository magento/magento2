<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ParallelProcess\Fork;

/**
 * Called when a fork processes finishes.
 */
interface ExitEventListenerInterface
{
    /**
     * @param int $forkId Fork ID which exited.
     * @param bool $successfulRun Whether fork process execution was successful.
     *
     * @return void
     */
    public function observe(int $forkId, bool $successfulRun);
}