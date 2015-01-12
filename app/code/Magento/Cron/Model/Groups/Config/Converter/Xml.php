<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\Groups\Config\Converter;

/**
 * Converts cron parameters from XML files
 */
class Xml implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Converting data to array type
     *
     * @param mixed $source
     * @return array
     * @throws \InvalidArgumentException
     */
    public function convert($source)
    {
        $output = [];

        if (!$source instanceof \DOMDocument) {
            return $output;
        }

        $groups = $source->getElementsByTagName('group');
        foreach ($groups as $group) {
            /** @var $group \DOMElement */
            if (!$group->hasAttribute('id')) {
                throw new \InvalidArgumentException('Attribute "id" does not exist');
            }
            foreach ($group->childNodes as $child) {
                if (!$child instanceof \DOMElement) {
                    continue;
                }
                /** @var $group \DOMElement */
                $output[$group->getAttribute('id')][$child->nodeName] = $child->nodeValue;
            }
        }
        return $output;
    }
}
