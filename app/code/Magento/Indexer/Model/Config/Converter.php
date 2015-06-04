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
                /** @var $childNode \DOMElement */
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
     * @param \DOMElement $childNode
     * @param array $data
     * @return array
     */
    protected function convertChild(\DOMElement $childNode, $data)
    {
        $data['sources']  = isset($data['sources']) ? $data['sources'] : [];
        $data['handlers'] = isset($data['handlers']) ? $data['handlers'] : [];
        switch ($childNode->nodeName) {
            case 'title':
                $data['title'] = $this->getTranslatedNodeValue($childNode);
                break;
            case 'description':
                $data['description'] = $this->getTranslatedNodeValue($childNode);
                break;
            case 'field':
                $data = $this->convertField($childNode, $data);
                break;
            case 'source':
                $data['sources'][$this->getAttributeValue($childNode, 'name')]
                    = $this->getAttributeValue($childNode, 'class');
                break;
            case 'handler':
                $data['handlers'][$this->getAttributeValue($childNode, 'name')]
                    = $this->getAttributeValue($childNode, 'class');
                break;
        }
        return $data;
    }

    /**
     * Convert field
     *
     * @param \DOMElement $node
     * @param array $data
     * @return array
     */
    protected function convertField(\DOMElement $node, $data)
    {
        $data['fields'] = isset($data['fields']) ? $data['fields'] : [];

        $data['fields'][$this->getAttributeValue($node, 'name')] = [
            'name'     => $this->getAttributeValue($node, 'name'),
            'source'   => $this->getAttributeValue($node, 'source'),
            'handler'  => $this->getAttributeValue($node, 'handler'),
            'dataType' => $this->getAttributeValue($node, 'dataType'),
            'type'     => $node->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'type'),
        ];

        $data['fields'][$this->getAttributeValue($node, 'name')]['filters'] = [];
        /** @var $childNode \DOMNode */
        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $data['fields'][$this->getAttributeValue($node, 'name')]['filters'][]
                = $this->getAttributeValue($childNode, 'class');
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
