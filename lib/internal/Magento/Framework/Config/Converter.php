<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

use Magento\Framework\View\Xsd\Media\TypeDataExtractorPool;

/**
 * Class Converter convert xml to appropriate array
 *
 * @package Magento\Framework\Config
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * @var \Magento\Framework\View\Xsd\Media\TypeDataExtractorPool
     */
    protected $extractorPool;

    /**
     * @param TypeDataExtractorPool $extractorPool
     */
    public function __construct(TypeDataExtractorPool $extractorPool)
    {
        $this->extractorPool = $extractorPool;
    }

    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \InvalidArgumentException
     */
    public function convert($source)
    {
        $xpath = new \DOMXPath($source);
        $output = [];
        foreach ($xpath->evaluate('/view') as $typeNode) {
            foreach ($typeNode->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $result = $this->parseNodes($childNode);
                $output = array_merge_recursive($output, $result);
            }
        }
        return $output;
    }

    /**
     * Parse node values from xml nodes
     *
     * @param \DOMElement $childNode
     * @return array
     */
    protected function parseNodes($childNode)
    {
        $output = [];
        switch ($childNode->nodeName) {
            case 'vars':
                $moduleName = $childNode->getAttribute('module');
                $output[$childNode->tagName][$moduleName] = $this->parseVarElement($childNode);
                break;
            case 'exclude':
                /** @var $itemNode \DOMElement */
                foreach ($childNode->getElementsByTagName('item') as $itemNode) {
                    $itemType = $itemNode->getAttribute('type');
                    $output[$childNode->tagName][$itemType][] = $itemNode->nodeValue;
                }
                break;
            case 'media':
                foreach ($childNode->childNodes as $mediaNode) {
                    if ($mediaNode instanceof \DOMElement) {
                        $mediaNodesArray =
                            $this->extractorPool->nodeProcessor($mediaNode->tagName)->process(
                                $mediaNode,
                                $childNode->tagName
                            );
                        $output = array_merge_recursive($output, $mediaNodesArray);
                    }
                }
                break;
        }
        return $output;
    }

    /**
     * Recursive parser for <var> nodes
     *
     * @param \DOMElement $node
     * @return string|boolean|number|null|[]
     */
    protected function parseVarElement(\DOMElement $node)
    {
        $result = [];
        for ($varNode = $node->firstChild; $varNode !== null; $varNode = $varNode->nextSibling) {
            if ($varNode instanceof \DOMElement && $varNode->tagName == "var") {
                $varName = $varNode->getAttribute('name');
                $result[$varName] = $this->parseVarElement($varNode);
            }
        }
        if (!count($result)) {
            $result = $node->nodeValue;
        }
        return $result;
    }
}
