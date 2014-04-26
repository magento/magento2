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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Config\Converter;

class Dom implements \Magento\Framework\Config\ConverterInterface
{
    const ATTRIBUTES = '__attributes__';

    const CONTENT = '__content__';

    /**
     * Convert dom node tree to array
     *
     * @param mixed $source
     * @return array
     */
    public function convert($source)
    {
        $nodeListData = array();

        /** @var $node \DOMNode */
        foreach ($source->childNodes as $node) {
            if ($node->nodeType == XML_ELEMENT_NODE) {
                $nodeData = array();
                /** @var $attribute \DOMNode */
                foreach ($node->attributes as $attribute) {
                    if ($attribute->nodeType == XML_ATTRIBUTE_NODE) {
                        $nodeData[self::ATTRIBUTES][$attribute->nodeName] = $attribute->nodeValue;
                    }
                }
                $childrenData = $this->convert($node);

                if (is_array($childrenData)) {
                    $nodeData = array_merge($nodeData, $childrenData);
                } else {
                    $nodeData[self::CONTENT] = $childrenData;
                }
                $nodeListData[$node->nodeName][] = $nodeData;
            } elseif ($node->nodeType == XML_CDATA_SECTION_NODE || $node->nodeType == XML_TEXT_NODE && trim(
                $node->nodeValue
            ) != ''
            ) {
                return $node->nodeValue;
            }
        }
        return $nodeListData;
    }
}
