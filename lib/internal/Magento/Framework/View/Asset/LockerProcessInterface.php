<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Asset;

/**
 * Interface LockerProcessInterface
 *
 * @api
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
