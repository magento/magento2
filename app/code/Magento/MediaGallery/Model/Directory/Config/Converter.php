<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory\Config;

use Magento\Framework\Config\ConverterInterface;

/**
 * Media gallery directory config converter
 */
class Converter implements ConverterInterface
{
    /**
     * Excluded list tag name
     */
    private const EXCLUDED_LIST_TAG_NAME = 'exclude';

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
    public function convert($source): array
    {
        $result = [];

        if (!$source instanceof \DOMDocument) {
            throw new \InvalidArgumentException('The source should be instance of DOMDocument');
        }

        foreach ($source->getElementsByTagName(self::EXCLUDED_LIST_TAG_NAME) as $excludedList) {
            $result[self::EXCLUDED_LIST_TAG_NAME] = [];
            foreach ($excludedList->getElementsByTagName(self::PATTERNS_TAG_NAME) as $patterns) {
                $result[self::EXCLUDED_LIST_TAG_NAME][self::PATTERNS_TAG_NAME] = [];
                foreach ($patterns->getElementsByTagName(self::PATTERN_TAG_NAME) as $pattern) {
                    $result[self::EXCLUDED_LIST_TAG_NAME][self::PATTERNS_TAG_NAME]
                    [$pattern->attributes->getNamedItem('name')->nodeValue] = $pattern->nodeValue;
                }
            }
        }

        return $result;
    }
}
