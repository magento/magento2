<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config\Reader\Xml;

use Magento\Framework\Config\ConverterInterface;
use Magento\Framework\Phrase;

/**
 * Converts MessageQueue config from \DOMDocument to array
 *
 * @deprecated 100.2.0
 */
class CompositeConverter implements ConverterInterface
{
    /**
     * @var ConverterInterface[]
     */
    private $converters;

    /**
     * Initialize dependencies.
     *
     * @param array $converters
     */
    public function __construct(array $converters)
    {
        $this->converters = [];
        $converters = $this->sortConverters($converters);
        foreach ($converters as $name => $converterInfo) {
            if (!isset($converterInfo['converter']) || !($converterInfo['converter'] instanceof ConverterInterface)) {
                throw new \InvalidArgumentException(
                    new Phrase(
                        'Converter [%name] must implement Magento\Framework\Config\ConverterInterface',
                        ['name' => $name]
                    )
                );
            }
            $this->converters[] = $converterInfo['converter'];
        }
    }

    /**
     * @inheritdoc
     */
    public function convert($source)
    {
        $result = [];
        foreach ($this->converters as $converter) {
            $result = array_replace_recursive($result, $converter->convert($source));
        }
        return $result;
    }

    /**
     * Sort converters according to param 'sortOrder'
     *
     * @param array $converters
     * @return array
     */
    private function sortConverters(array $converters)
    {
        usort(
            $converters,
            function ($firstItem, $secondItem) {
                $firstValue = 0;
                $secondValue = 0;
                if (isset($firstItem['sortOrder'])) {
                    $firstValue = (int)$firstItem['sortOrder'];
                }
                if (isset($secondItem['sortOrder'])) {
                    $secondValue = (int)$secondItem['sortOrder'];
                }
                return $firstValue <=> $secondValue;
            }
        );
        return $converters;
    }
}
