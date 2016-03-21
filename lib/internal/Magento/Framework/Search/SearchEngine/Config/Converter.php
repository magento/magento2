<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\SearchEngine\Config;

use Magento\Framework\Config\ConverterInterface;

class Converter implements ConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert($source)
    {
        $result = [];
        /** @var \DOMElement $engine */
        foreach ($source->documentElement->getElementsByTagName('engine') as $engine) {
            $name = $engine->getAttribute('name');
            $result[$name] = [];
            /** @var \DOMElement $feature */
            foreach ($engine->getElementsByTagName('feature') as $feature) {
                if ($feature->getAttribute('support') === '1'
                    || strtolower($feature->getAttribute('support')) === 'true'
                ) {
                    $result[$name][] = strtolower($feature->getAttribute('name'));
                }
            }
        }
        return $result;
    }
}
