<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

/**
 * Interface LockerProcessInterface
 * @since 2.0.0
 */
interface LockerProcessInterface
{
    /**
     * @param string $lockName
     * @return void
     * @since 2.0.0
     */
    public function lockProcess($lockName);

    /**
     * @return void
     * @since 2.0.0
     */
    public function unlockProcess();
}
