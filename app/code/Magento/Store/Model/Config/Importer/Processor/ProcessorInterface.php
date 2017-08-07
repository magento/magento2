<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer\Processor;

use Magento\Framework\Exception\RuntimeException;

/**
 * The processor for store manipulations.
 * @since 2.2.0
 */
interface ProcessorInterface
{
    /**
     * Runs current process.
     *
     * @param array $data The data to be processed
     * @return void
     * @throws RuntimeException If processor was unable to finish execution
     * @since 2.2.0
     */
    public function run(array $data);
}
