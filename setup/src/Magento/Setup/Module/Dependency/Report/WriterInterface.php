<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report;

use Magento\Setup\Module\Dependency\Report\Data\ConfigInterface;

/**
 *  Writer Interface
 */
interface WriterInterface
{
    /**
     * Write a report file
     *
     * @param array $options
     * @param \Magento\Setup\Module\Dependency\Report\Data\ConfigInterface $config
     * @return void
     */
    public function write(array $options, ConfigInterface $config);
}
