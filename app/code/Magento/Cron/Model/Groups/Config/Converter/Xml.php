<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\Groups\Config\Converter;

/**
 * Converts cron parameters from XML files
 * @since 2.0.0
 */
class Xml implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Converting data to array type
     *
     * @param mixed $source
     * @return array
     * @throws \InvalidArgumentException
     * @since 2.0.0
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
                $output[$group->getAttribute('id')][$child->nodeName]['value'] = $child->nodeValue;
                if ($child->hasAttribute('tooltip')) {
                    $output[$group->getAttribute('id')][$child->nodeName]['tooltip'] = $child->getAttribute('tooltip');
                }
            }
        }
        return $output;
    }
}
