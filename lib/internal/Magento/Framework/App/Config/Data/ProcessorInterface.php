<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Data;

/**
 * Processes data from admin store configuration fields
 *
 * @api
 */
interface ProcessorInterface
{
    /**
     * Process config value
     *
     * @param string $value Raw value of the configuration field
     * @return string Processed value
     */
    public function processValue($value);
}
