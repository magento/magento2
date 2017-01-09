<?php
/**
 * DB configuration data converter. Converts associative array to tree array
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config;

/**
 * Class Converter.
 */
class Converter extends \Magento\Framework\App\Config\Scope\Converter
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
