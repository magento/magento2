<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Converts sales totals (incl. nominal, creditmemo, invoice) from \DOMDocument to array
 */
namespace Magento\Sales\Model\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
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
        $output = array();
        if (!$source instanceof \DOMDocument) {
            return $output;
        }

        /** @var \DOMNodeList $sections*/
        $sections = $source->getElementsByTagName('section');

        /** @var \DOMElement $section */
        foreach ($sections as $section) {
            $sectionArray = array();
            $sectionName = $section->getAttribute('name');

            if (!$sectionName) {
                throw new \InvalidArgumentException('Attribute "name" of "section" does not exist');
            }

            /** @var \DOMNodeList $groups */
            $groups = $section->getElementsByTagName('group');
            /** @var \DOMElement $group */

            foreach ($groups as $group) {
                $groupArray = array();
                $groupName = $group->getAttribute('name');
                if (!$groupName) {
                    throw new \InvalidArgumentException('Attribute "name" of "group" does not exist');
                }

                /** @var \DOMNodeList $items */
                $items = $group->getElementsByTagName('item');
                /** @var \DOMElement $item */

                foreach ($items as $item) {
                    $rendererArray = array();
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

                    $itemArray = array(
                        'instance' => $item->getAttribute('instance'),
                        'sort_order' => $item->getAttribute('sort_order'),
                        'renderers' => $rendererArray
                    );
                    $groupArray[$itemName] = $itemArray;
                }
                $sectionArray[$groupName] = $groupArray;
            }
            $output[$sectionName] = $sectionArray;
        }

        $order = $source->getElementsByTagName('order')->item(0);
        $availableProductTypes = array();
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
