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
 * @since 2.0.0
 */
interface OutputInterface
{
    /**
     * Display profiling results in appropriate format
     *
     * @param Stat $stat
     * @return void
     * @since 2.0.0
     */
    public function display(Stat $stat);
}
