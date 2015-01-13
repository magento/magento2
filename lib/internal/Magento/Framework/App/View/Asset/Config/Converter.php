<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App\View\Asset\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \InvalidArgumentException
     */
    public function convert($source)
    {
        $output = [];
        $exclude = $source->getElementsByTagName('exclude')->item(0);

        foreach ($exclude->childNodes as $entity) {
            if ($entity->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            foreach ($entity->childNodes as $area) {
                if ($area->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                foreach ($area->childNodes as $item) {
                    if ($item->nodeType != XML_ELEMENT_NODE) {
                        continue;
                    }
                    $output[$entity->tagName][$area->tagName][] = $item->nodeValue;
                }
            }
        }
        return $output;
    }
}
