<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mtf\Client\Driver\Selenium\Element;

use Mtf\Client\Driver\Selenium\Element;
use Mtf\Client\Element as ElementInterface;

/**
 * Class Tree
 * General class for tree elements. Holds general implementation of methods, which overrides in child classes.
 *
 * @package Mtf\Client\Driver\Selenium\Element
 */
abstract class Tree extends Element
{
    /**
     * Css class for finding tree nodes
     *
     * @var string
     */
    protected $nodeCssClass;

    /**
     * Css class for detecting tree nodes
     *
     * @var string
     */
    protected $nodeSelector;

    /**
     * Css class for fetching node's name
     *
     * @var string
     */
    protected $nodeName;

    /**
     * @return mixed
     */
    abstract public function getStructure();

    /**
     * Drag'n'drop method is not accessible in this class.
     * Throws exception if used.
     *
     * @param ElementInterface $target
     * @throws \BadMethodCallException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function dragAndDrop(ElementInterface $target)
    {
        throw new \BadMethodCallException('Not applicable for this class of elements (TreeElement)');
    }

    /**
     * getValue method is not accessible in this class.
     * Throws exception if used.
     *
     * @throws \BadMethodCallException
     */
    public function getValue()
    {
        throw new \BadMethodCallException('Not applicable for this class of elements (TreeElement)');
    }

    /**
     * keys method is not accessible in this class.
     * Throws exception if used.
     *
     * @param array $keys
     * @throws \BadMethodCallException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function keys(array $keys)
    {
        throw new \BadMethodCallException('Not applicable for this class of elements (TreeElement)');
    }

    /**
     * Click a tree element by its path (Node names) in tree
     *
     * @param string $path
     * @throws \InvalidArgumentException
     */
    public function setValue($path)
    {
        $pathChunkCounter = 0;
        $pathArray = explode('/', $path);
        $pathArrayLength = count($pathArray);
        $structureChunk = $this->getStructure(); //Set the root of a structure as a first structure chunk
        foreach ($pathArray as $pathChunk) {
            $structureChunk = $this->deep($pathChunk, $structureChunk);
            $structureChunk = ($pathChunkCounter == $pathArrayLength - 1) ?
                $structureChunk['element'] : $structureChunk['subnodes'];
            ++$pathChunkCounter;
        }
        if ($structureChunk) {
            $needleElement = $structureChunk->find($this->nodeName);
            $needleElement->click();
        } else {
            throw new \InvalidArgumentException('The path specified for tree is invalid');
        }
    }

    /**
     * Internal function for deeping in hierarchy of the tree structure
     * Return the nested array if it exists or object of class Element if this is the final part of structure
     *
     * @param string $pathChunk
     * @param array $structureChunk
     * @return array|Element||false
     */
    protected function deep($pathChunk, $structureChunk)
    {
        if (is_array($structureChunk)) {
            foreach ($structureChunk as $structureNode) {
                $pattern = '/' . $pathChunk . '\s\([\d]+\)|' . $pathChunk . '/';
                if (isset($structureNode) && preg_match($pattern, $structureNode['name'])) {
                    return $structureNode;
                }
            }
        }
        return false;
    }

    /**
     *  Recursive walks tree
     *
     * @param Element $node
     * @param string $parentCssClass
     * @return array
     */
    protected function _getNodeContent($node, $parentCssClass)
    {
        $nodeArray = [];
        $nodeList = [];
        $counter = 1;
        $newNode = $node->find($parentCssClass . ' > ' . $this->nodeSelector . ':nth-of-type(' . $counter . ')');
        //Get list of all children nodes to work with
        while ($newNode->isVisible()) {
            $nodeList[] = $newNode;
            ++$counter;
            $newNode = $node->find($parentCssClass . ' > ' . $this->nodeSelector . ':nth-of-type(' . $counter . ')');
        }
        //Write to array values of current node
        foreach ($nodeList as $currentNode) {
            /** @var Element $currentNode */
            $nodesNames = $currentNode->find($this->nodeName);
            $nodesContents = $currentNode->find($this->nodeCssClass);
            $text = ltrim($nodesNames->getText());
            $nodeArray[] = [
                'name' => $text,
                'element' => $currentNode,
                'subnodes' => $nodesContents->isVisible() ?
                        $this->_getNodeContent($nodesContents, $this->nodeCssClass) : null,
            ];
        }
        return $nodeArray;
    }
}
