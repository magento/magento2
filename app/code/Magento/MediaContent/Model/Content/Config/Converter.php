<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model\Content\Config;

use Magento\Framework\Config\ConverterInterface;

/**
 * Class Converter
 */
class Converter implements ConverterInterface
{
    /**
     * Search tag name
     */
    private const SEARCH_TAG_NAME = 'search';

    /**
     * Patterns tag name
     */
    private const PATTERNS_TAG_NAME = 'patterns';

    /**
     * Pattern tag name
     */
    private const PATTERN_TAG_NAME = 'pattern';

    /**
     * Convert dom node to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source) : array
    {
        $result = [];

        if (!$source instanceof \DOMDocument) {
            return $result;
        }

        foreach ($source->getElementsByTagName(self::SEARCH_TAG_NAME) as $blacklist) {
            $result[self::SEARCH_TAG_NAME] = [];
            foreach ($blacklist->getElementsByTagName(self::PATTERNS_TAG_NAME) as $patterns) {
                $result[self::SEARCH_TAG_NAME][self::PATTERNS_TAG_NAME] = [];
                foreach ($patterns->getElementsByTagName(self::PATTERN_TAG_NAME) as $pattern) {
                    $result[self::SEARCH_TAG_NAME][self::PATTERNS_TAG_NAME]
                    [$pattern->attributes->getNamedItem('name')->nodeValue] = $pattern->nodeValue;
                }
            }
        }

        return $result;
    }
}