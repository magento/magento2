<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report;

use Magento\Setup\Module\Dependency\Report\Data\ConfigInterface;

/**
 *  Writer Interface
 * @since 2.0.0
 */
interface WriterInterface
{
    /**
     * Write a report file
     *
     * @param array $options
     * @param \Magento\Setup\Module\Dependency\Report\Data\ConfigInterface $config
     * @return void
     * @since 2.0.0
     */
    public function write(array $options, ConfigInterface $config);
}
