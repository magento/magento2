<?php
/**
 * Interface for output class of standard profiler driver.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Driver\Standard;

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
