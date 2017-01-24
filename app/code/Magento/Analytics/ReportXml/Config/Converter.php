<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml\Config;

use Magento\Framework\Config\ConverterInterface;

/**
 * Class Converter
 *
 * Composite converter for config
 */
class Converter implements ConverterInterface
{
    /**
     * @var ConverterInterface[]
     */
    private $converters;

    /**
     * Converter constructor.
     *
     * @param ConverterInterface[] $converters
     */
    public function __construct(
        $converters
    ) {
        $this->converters = $converters;
    }

    /**
     * Convert config
     *
     * @param $source
     * @return array
     */
    public function convert($source)
    {
        $data = [];
        foreach ($this->converters as $converter) {
            $data = array_merge_recursive($data, $converter->convert($source));
        }
        return $data;
    }
}
