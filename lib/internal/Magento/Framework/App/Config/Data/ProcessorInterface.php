<?php
/**
 * Processor interface
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
