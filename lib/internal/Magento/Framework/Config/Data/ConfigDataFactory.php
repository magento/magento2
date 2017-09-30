<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config\Data;

/**
 * Factory for ConfigData
 * @api
 */
class ConfigDataFactory
{
    /**
     * Returns a new instance of ConfigData on every call.
     *
     * @param string $fileKey
     * @return ConfigData
     */
    public function create(string $fileKey)
    {
        return new ConfigData($fileKey);
    }
}
