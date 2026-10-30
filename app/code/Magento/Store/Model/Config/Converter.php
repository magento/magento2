<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
