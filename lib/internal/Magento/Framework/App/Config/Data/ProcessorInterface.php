<?php
/**
 * Processor interface
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Data;

interface ProcessorInterface
{
    /**
     * Process config value
     *
     * @param string $value
     * @return string
     */
    public function processValue($value);
}
