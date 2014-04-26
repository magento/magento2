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
namespace Magento\Indexer\Model\Config;

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
        $xpath = new \DOMXPath($source);
        $indexers = $xpath->evaluate('/config/indexer');
        /** @var $typeNode \DOMNode */
        foreach ($indexers as $indexerNode) {
            $data = array();
            $indexerId = $this->getAttributeValue($indexerNode, 'id');
            $data['indexer_id'] = $indexerId;
            $data['view_id'] = $this->getAttributeValue($indexerNode, 'view_id');
            $data['action_class'] = $this->getAttributeValue($indexerNode, 'class');
            $data['title'] = '';
            $data['description'] = '';

            /** @var $childNode \DOMNode */
            foreach ($indexerNode->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                $data = $this->convertChild($childNode, $data);
            }
            $output[$indexerId] = $data;
        }
        return $output;
    }

    /**
     * Get attribute value
     *
     * @param \DOMNode $input
     * @param string $attributeName
     * @param mixed $default
     * @return null|string
     */
    protected function getAttributeValue(\DOMNode $input, $attributeName, $default = null)
    {
        $node = $input->attributes->getNamedItem($attributeName);
        return $node ? $node->nodeValue : $default;
    }

    /**
     * Convert child from dom to array
     *
     * @param \DOMNode $childNode
     * @param array $data
     * @return array
     */
    protected function convertChild(\DOMNode $childNode, $data)
    {
        switch ($childNode->nodeName) {
            case 'title':
                $data['title'] = $this->getTranslatedNodeValue($childNode);
                break;
            case 'description':
                $data['description'] = $this->getTranslatedNodeValue($childNode);
                break;
        }
        return $data;
    }

    /**
     * Return node value translated if applicable
     *
     * @param \DOMNode $node
     * @return string
     */
    protected function getTranslatedNodeValue(\DOMNode $node)
    {
        $value = $node->nodeValue;
        if ($this->getAttributeValue($node, 'translate') == 'true') {
            $value = __($value);
        }
        return $value;
    }
}
