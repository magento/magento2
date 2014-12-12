<?php
/**
 * Interface for output class of standard profiler driver.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
