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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Config\Dom\Converter;

class ArrayConverter
{
    const ATTRIBUTES = '__attributes__';
    const CONTENT = '__content__';

    /**
     * Convert dom node tree to array
     *
     * @param \DOMNodeList $input
     * @return array
     */
    public function convert(\DOMNodeList $input)
    {
        $array = array();

        /** @var $item \DOMNode */
        foreach ($input as $item) {
            if ($item->nodeType == XML_ELEMENT_NODE) {
                $arrayElement = array();
                /** @var $attribute \DOMNode */
                foreach ($item->attributes as $attribute) {
                    if ($attribute->nodeType == XML_ATTRIBUTE_NODE) {
                        $arrayElement[self::ATTRIBUTES][$attribute->nodeName] = $attribute->nodeValue;
                    }
                }
                $children = $this->convert($item->childNodes);

                if (is_array($children)) {
                    $arrayElement = array_merge($arrayElement, $children);
                } else {
                    $arrayElement[self::CONTENT] = $children;
                }
                $array[$item->nodeName][] = $arrayElement;
            } elseif ($item->nodeType == XML_CDATA_SECTION_NODE
                || ($item->nodeType == XML_TEXT_NODE && trim($item->nodeValue) != '')
            ) {
                return $item->nodeValue;
            }
        }
        return $array;
    }
}
