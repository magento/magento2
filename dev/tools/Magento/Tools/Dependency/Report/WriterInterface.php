<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Dependency\Report;

use Magento\Tools\Dependency\Report\Data\ConfigInterface;

/**
 *  Writer Interface
 */
interface WriterInterface
{
    /**
     * Write a report file
     *
     * @param array $options
     * @param \Magento\Tools\Dependency\Report\Data\ConfigInterface $config
     * @return void
     */
    public function write(array $options, ConfigInterface $config);
}
