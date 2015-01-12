<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        $output = [];
        $xpath = new \DOMXPath($source);
        $indexers = $xpath->evaluate('/config/indexer');
        /** @var $typeNode \DOMNode */
        foreach ($indexers as $indexerNode) {
            $data = [];
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
