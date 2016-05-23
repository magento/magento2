<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Config;

/**
 * Search Request xml converter
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert config
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        /** @var \DOMNodeList $requestNodes */
        $requestNodes = $source->getElementsByTagName('request');
        $requests = [];
        foreach ($requestNodes as $requestNode) {
            $simpleXmlNode = simplexml_import_dom($requestNode);
            /** @var \DOMElement $requestNode */
            $name = $requestNode->getAttribute('query');
            $request = $this->mergeAttributes((array)$simpleXmlNode);
            $request['dimensions'] = $this->convertNodes($simpleXmlNode->dimensions, 'name');
            $request['queries'] = $this->convertNodes($simpleXmlNode->queries, 'name');
            $request['filters'] = $this->convertNodes($simpleXmlNode->filters, 'name');
            $request['aggregations'] = $this->convertNodes($simpleXmlNode->aggregations, 'name');
            $requests[$name] = $request;
        }
        return $requests;
    }

    /**
     * Merge attributes in node data
     *
     * @param array $data
     * @return array
     */
    protected function mergeAttributes($data)
    {
        if (isset($data['@attributes'])) {
            $data = array_merge($data, $data['@attributes']);
            unset($data['@attributes']);
        }
        return $data;
    }

    /**
     * Convert nodes to array
     *
     * @param \SimpleXMLElement $nodes
     * @param string $name
     * @return array
     */
    protected function convertNodes(\SimpleXMLElement $nodes, $name)
    {
        $list = [];
        if (!empty($nodes)) {
            /** @var \SimpleXMLElement $node */
            foreach ($nodes->children() as $node) {
                $element = $this->convertToArray($node->attributes());
                if ($node->count() > 0) {
                    $element = $this->convertChildNodes($element, $node);
                }
                $type = (string)$node->attributes('xsi', true)['type'];
                if (!empty($type)) {
                    $element['type'] = $type;
                }

                $list[$element[$name]] = $element;
            }
        }
        return $list;
    }

    /**
     * Deep converting simlexml element to array
     *
     * @param \SimpleXMLElement $node
     * @return array
     */
    protected function convertToArray(\SimpleXMLElement $node)
    {
        return $this->mergeAttributes(json_decode(json_encode($node), true));
    }

    /**
     * Convert child nodes to array
     *
     * @param array $element
     * @param \SimpleXMLElement $node
     * @return array
     */
    protected function convertChildNodes(array $element, \SimpleXMLElement $node)
    {
        if ($node->count() == 0) {
            $element[$node->getName()][] = $this->convertToArray($node);
        } else {
            foreach ($node->children() as $child) {
                $element = $this->convertChildNodes($element, $child);
            }
        }
        return $element;
    }
}
