<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ProductTypes\Config;

/**
 * Class \Magento\Catalog\Model\ProductTypes\Config\Converter
 *
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
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
        $output = [];
        $xpath = new \DOMXPath($source);
        $types = $xpath->evaluate('/config/type');
        /** @var $typeNode \DOMNode */
        foreach ($types as $typeNode) {
            $typeName = $this->_getAttributeValue($typeNode, 'name');
            $isComposite = $this->_getAttributeValue($typeNode, 'composite', 'false');
            $isDecimal = $this->_getAttributeValue($typeNode, 'canUseQtyDecimals', 'true');
            $isQty = $this->_getAttributeValue($typeNode, 'isQty', 'false');
            $data = [];
            $data['name'] = $typeName;
            $data['label'] = $this->_getAttributeValue($typeNode, 'label', '');
            $data['model'] = $this->_getAttributeValue($typeNode, 'modelInstance');
            $data['composite'] = !empty($isComposite) && 'false' !== $isComposite;
            $data['index_priority'] = (int)$this->_getAttributeValue($typeNode, 'indexPriority', 0);
            $data['can_use_qty_decimals'] = !empty($isDecimal) && 'false' !== $isDecimal;
            $data['is_qty'] = !empty($isQty) && 'false' !== $isQty;
            $data['sort_order'] = (int)$this->_getAttributeValue($typeNode, 'sortOrder', 0);

            /** @var $childNode \DOMNode */
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
                    case 'allowedSelectionTypes':
                        /** @var $selectionsTypes \DOMNode */
                        foreach ($childNode->childNodes as $selectionsTypes) {
                            if ($selectionsTypes->nodeType != XML_ELEMENT_NODE) {
                                continue;
                            }
                            $name = $this->_getAttributeValue($selectionsTypes, 'name');
                            $data['allowed_selection_types'][$name] = $name;
                        }
                        break;
                    case 'customAttributes':
                        /** @var $customAttributes \DOMNode */
                        foreach ($childNode->childNodes as $customAttributes) {
                            if ($customAttributes->nodeType != XML_ELEMENT_NODE) {
                                continue;
                            }
                            $name = $this->_getAttributeValue($customAttributes, 'name');
                            $value = $this->_getAttributeValue($customAttributes, 'value');
                            $data['custom_attributes'][$name] = $value;
                        }
                        break;
                }
            }
            $output['types'][$typeName] = $data;
        }

        $composableTypes = $xpath->evaluate('/config/composableTypes/*');
        $output['composableTypes'] = [];
        foreach ($composableTypes as $typeNode) {
            $typeName = $this->_getAttributeValue($typeNode, 'name');
            $output['composableTypes'][$typeName] = $typeName;
        }

        return $output;
    }

    /**
     * Get attribute value
     *
     * @param \DOMNode $input
     * @param string $attributeName
     * @param string|null $default
     * @return null|string
     */
    protected function _getAttributeValue(\DOMNode $input, $attributeName, $default = null)
    {
        $node = $input->attributes->getNamedItem($attributeName);
        return $node ? $node->nodeValue : $default;
    }
}
