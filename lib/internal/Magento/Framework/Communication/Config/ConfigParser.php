<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication\Config;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Parser helper for communication-related configs.
 * @since 2.2.0
 */
class ConfigParser
{
    const TYPE_NAME = 'typeName';
    const METHOD_NAME = 'methodName';

    /**
     * Parse service method name.
     *
     * @param string $serviceMethod
     * @return array Contains class name and method name
     * @throws LocalizedException
     * @since 2.2.0
     */
    public function parseServiceMethod($serviceMethod)
    {
        $pattern = '/^([a-zA-Z]+[a-zA-Z0-9\\\\]+)::([a-zA-Z0-9]+)$/';
        preg_match($pattern, $serviceMethod, $matches);
        if (!isset($matches[1]) || !isset($matches[2])) {
            throw new LocalizedException(
                new Phrase(
                    'Service method "%serviceMethod" must match the following pattern: "%pattern"',
                    ['serviceMethod' => $serviceMethod, 'pattern' => $pattern]
                )
            );
        }
        $className = $matches[1];
        $methodName = $matches[2];
        return [self::TYPE_NAME => $className, self::METHOD_NAME => $methodName];
    }
}
