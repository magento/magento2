<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
