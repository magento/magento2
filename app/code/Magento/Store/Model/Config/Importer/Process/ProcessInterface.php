<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer\Process;

use Magento\Framework\Exception\RuntimeException;

/**
 * The processor for store manipulations.
 */
interface ProcessInterface
{
    /**
     * Runs current process.
     *
     * @param array $data The data to be processed
     * @return void
     * @throws RuntimeException If process failed
     */
    public function run(array $data);
}
