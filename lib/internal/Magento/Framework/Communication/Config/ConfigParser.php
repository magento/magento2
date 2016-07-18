<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication\Config;
use Magento\Framework\Phrase;

/**
 * Parser helper for communication-related configs.
 */
class ConfigParser
{
    const SERVICE_METHOD_NAME_PATTERN = '/^([a-zA-Z\\\\]+)::([a-zA-Z]+)$/';

    /**
     * Parse service method name.
     *
     * @param string $serviceMethod
     * @return array Contains class name and method name
     */
    public function parseServiceMethod($serviceMethod)
    {
        preg_match(self::SERVICE_METHOD_NAME_PATTERN, $serviceMethod, $matches);
        // service method format is validated by XSD, so extra validation here may be omitted
        $className = $matches[1];
        $methodName = $matches[2];
        return ['type' => $className, 'method' => $methodName];
    }
}
