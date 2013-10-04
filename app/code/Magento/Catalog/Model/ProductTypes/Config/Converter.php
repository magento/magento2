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
namespace Magento\Catalog\Model\ProductTypes\Config;

class Converter implements \Magento\Config\ConverterInterface
{
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \InvalidArgumentException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function convert($source)
    {
        $output = array();
        $xpath = new \DOMXPath($source);
        $types = $xpath->evaluate('/config/type');
        /** @var $typeNode DOMNode */
        foreach ($types as $typeNode) {
            $typeName = $this->_getAttributeValue($typeNode, 'name');
            $data = array();
            $data['name'] = $typeName;
            $data['label'] = $this->_getAttributeValue($typeNode, 'label', '');
            $data['model'] = $this->_getAttributeValue($typeNode, 'modelInstance');
            $data['composite'] = (bool) $this->_getAttributeValue($typeNode, 'composite', false);
            $data['index_priority'] = (int) $this->_getAttributeValue($typeNode, 'indexPriority', 0);
            $data['can_use_qty_decimals'] = (bool) $this->_getAttributeValue($typeNode, 'canUseQtyDecimals', false);
            $data['is_qty'] = (bool) $this->_getAttributeValue($typeNode, 'isQty', false);

            /** @var $childNode DOMNode */
            foreach ($typeNode->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                switch ($childNode->nodeName) {
                    case 'priceModel':
                        $data['price_model'] = $this->_getAttributeValue($childNode, 'instance');
                        break;
                    case 'indexerModel':
                        $data['price_indexer'] = $this->_getAttributeValue($childNode, 'instance');
                        break;
                    case 'stockIndexerModel':
                        $data['stock_indexer'] = $this->_getAttributeValue($childNode, 'instance');
                        break;
                    case 'allowProductTypes':
                        /** @var $allowedTypes DOMNode */
                        foreach ($childNode->childNodes as $allowedTypes) {
                            if ($allowedTypes->nodeType != XML_ELEMENT_NODE) {
                                continue;
                            }
                            $name = $this->_getAttributeValue($allowedTypes, 'name');
                            $data['allow_product_types'][$name] = $name;
                        }
                        break;
                    case 'allowedSelectionTypes':
                        /** @var $selectionsTypes DOMNode */
                        foreach ($childNode->childNodes as $selectionsTypes) {
                            if ($selectionsTypes->nodeType != XML_ELEMENT_NODE) {
                                continue;
                            }
                            $name = $this->_getAttributeValue($selectionsTypes, 'name');
                            $data['allowed_selection_types'][$name] = $name;
                        }
                        break;
                }

            }
            $output[$typeName] = $data;
        }
        return $output;
    }

    /**
     * Get attribute value
     *
     * @param DOMNode $input
     * @param string $attributeName
     * @param mixed $default
     * @return null|string
     */
    protected function _getAttributeValue(\DOMNode $input, $attributeName, $default = null)
    {
        $node = $input->attributes->getNamedItem($attributeName);
        return $node ? $node->nodeValue : $default;
    }
}
