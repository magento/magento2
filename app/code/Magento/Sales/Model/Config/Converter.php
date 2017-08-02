<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Converts sales totals (incl. creditmemo, invoice) from \DOMDocument to array
 */
namespace Magento\Sales\Model\Config;

/**
 * Class \Magento\Sales\Model\Config\Converter
 *
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Converting data to array type
     *
     * @param mixed $source
     * @return array
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function convert($source)
    {
        $output = [];
        if (!$source instanceof \DOMDocument) {
            return $output;
        }

        /** @var \DOMNodeList $sections*/
        $sections = $source->getElementsByTagName('section');

        /** @var \DOMElement $section */
        foreach ($sections as $section) {
            $sectionArray = [];
            $sectionName = $section->getAttribute('name');

            if (!$sectionName) {
                throw new \InvalidArgumentException('Attribute "name" of "section" does not exist');
            }

            /** @var \DOMNodeList $groups */
            $groups = $section->getElementsByTagName('group');
            /** @var \DOMElement $group */

            foreach ($groups as $group) {
                $groupArray = [];
                $groupName = $group->getAttribute('name');
                if (!$groupName) {
                    throw new \InvalidArgumentException('Attribute "name" of "group" does not exist');
                }

                /** @var \DOMNodeList $items */
                $items = $group->getElementsByTagName('item');
                /** @var \DOMElement $item */

                foreach ($items as $item) {
                    $rendererArray = [];
                    $itemName = $item->getAttribute('name');
                    if (!$itemName) {
                        throw new \InvalidArgumentException('Attribute "name" of "item" does not exist');
                    }

                    /** @var \DOMNodeList $renderers */
                    $renderers = $item->getElementsByTagName('renderer');
                    /** @var \DOMElement $renderer */
                    foreach ($renderers as $renderer) {
                        $rendererName = $renderer->getAttribute('name');
                        if (!$rendererName) {
                            throw new \InvalidArgumentException('Attribute "name" of "renderer" does not exist');
                        }
                        $rendererArray[$rendererName] = $renderer->getAttribute('instance');
                    }

                    $itemArray = [
                        'instance' => $item->getAttribute('instance'),
                        'sort_order' => $item->getAttribute('sort_order'),
                        'renderers' => $rendererArray,
                    ];
                    $groupArray[$itemName] = $itemArray;
                }
                $sectionArray[$groupName] = $groupArray;
            }
            $output[$sectionName] = $sectionArray;
        }

        $order = $source->getElementsByTagName('order')->item(0);
        $availableProductTypes = [];
        /** @var \DOMElement $order */
        if ($order) {
            /** @var \DOMNodeList $types */
            $types = $order->getElementsByTagName('available_product_type');

            /** @var \DOMElement $type */
            foreach ($types as $type) {
                $availableProductTypes[] = $type->getAttribute('name');
            }
            $output['order']['available_product_types'] = $availableProductTypes;
        }

        return $output;
    }
}
