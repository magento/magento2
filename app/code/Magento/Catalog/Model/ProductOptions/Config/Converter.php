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
namespace Magento\Catalog\Model\ProductOptions\Config;

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
        $output = array();

        /** @var $optionNode \DOMNode */
        foreach ($source->getElementsByTagName('option') as $optionNode) {
            $optionName = $this->_getAttributeValue($optionNode, 'name');
            $data = array();
            $data['name'] = $optionName;
            $data['label'] = $this->_getAttributeValue($optionNode, 'label');
            $data['renderer'] = $this->_getAttributeValue($optionNode, 'renderer');

            /** @var $childNode \DOMNode */
            foreach ($optionNode->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $inputTypeName = $this->_getAttributeValue($childNode, 'name');
                $data['types'][$inputTypeName] = array(
                    'name' => $inputTypeName,
                    'label' => $this->_getAttributeValue($childNode, 'label'),
                    'disabled' => 'true' == $this->_getAttributeValue($childNode, 'disabled', 'false') ? true : false
                );
            }
            $output[$optionName] = $data;
        }
        return $output;
    }

    /**
     * Get attribute value
     *
     * @param \DOMNode $node
     * @param string $attributeName
     * @param string|null $defaultValue
     * @return null|string
     */
    protected function _getAttributeValue(\DOMNode $node, $attributeName, $defaultValue = null)
    {
        $attributeNode = $node->attributes->getNamedItem($attributeName);
        $output = $defaultValue;
        if ($attributeNode) {
            $output = $attributeNode->nodeValue;
        }
        return $output;
    }
}
