<?php
/**
 * Interface for output class of standard profiler driver.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Driver\Standard;

/**
 * Interface \Magento\Framework\Profiler\Driver\Standard\OutputInterface
 *
 */
interface OutputInterface
{
    /**
     * Display profiling results in appropriate format
     *
     * @param Stat $stat
     * @return void
     */
    public function display(Stat $stat);
}
