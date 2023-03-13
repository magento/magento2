<?php
/**
 * DB configuration data converter. Converts associative array to tree array
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config;

use Magento\Framework\App\Config\Scope\Converter as ConfigScopeConverter;

/**
 * Class Converter.
 */
class Converter extends ConfigScopeConverter
{
    /**
     * Convert config data
     *
     * @param array $source
     * @param array $initialConfig
     * @return array
     */
    public function convert($source, $initialConfig = [])
    {
        return array_replace_recursive($initialConfig, parent::convert($source));
    }
}
