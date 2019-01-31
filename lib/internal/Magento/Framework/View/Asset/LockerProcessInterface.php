<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

/**
 * Interface LockerProcessInterface
 */
interface LockerProcessInterface
{
    /**
     * @param string $lockName
     * @return void
     */
    public function lockProcess($lockName);

    /**
     * @return void
     */
    public function unlockProcess();
}
