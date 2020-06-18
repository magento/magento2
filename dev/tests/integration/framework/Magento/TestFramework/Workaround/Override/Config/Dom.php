<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround\Override\Config;

use Magento\Framework\Config\Dom as ParentDom;

/**
 * Class override nodes merge behaviour
 */
class Dom extends ParentDom
{
    /**
     * @inheritdoc
     */
    protected function _mergeNode(\DOMElement $node, $parentPath)
    {
        $path = $this->_getNodePathByParent($node, $parentPath);
        $matchedNodes = $this->getMatchedNodes($path);
        /* Update matched node attributes and value */
        if ($matchedNodes) {
            if (!$node->hasChildNodes()) {
                $parentMatchedNode = $this->_getMatchedNode($parentPath);
                $newNode = $this->dom->importNode($node, true);
                $parentMatchedNode->appendChild($newNode);
                return;
            }
            /* recursive merge for all child nodes */
            foreach ($node->childNodes as $childNode) {
                if ($childNode instanceof \DOMElement) {
                    $this->_mergeNode($childNode, $path);
                }
            }
        } else {
            /* Add node as is to the document under the same parent element */
            $parentMatchedNode = $this->_getMatchedNode($parentPath);
            $newNode = $this->dom->importNode($node, true);
            $parentMatchedNode->appendChild($newNode);
        }
    }

    /**
     * Get matched nodes
     *
     * @param string $nodePath
     * @return array
     */
    private function getMatchedNodes(string $nodePath): array
    {
        $xPath = new \DOMXPath($this->dom);
        if ($this->rootNamespace) {
            $xPath->registerNamespace(self::ROOT_NAMESPACE_PREFIX, $this->rootNamespace);
        }
        $nodes = [];
        $matchedNodes = $xPath->query($nodePath);
        foreach ($matchedNodes as $matchedNode) {
            $nodes[] = $matchedNode;
        }

        return $nodes;
    }
}
