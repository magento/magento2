<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mtf\Client\Driver\Selenium\Element;

use Mtf\Client\Element;

/**
 * Class JquerytreeElement
 * Typified element class for JqueryTree elements
 *
 * @package Mtf\Client\Element
 */
class JquerytreeElement extends Tree
{
    /**
     * Css class for finding tree nodes.
     *
     * @var string
     */
    protected $nodeCssClass = ' > ul';

    /**
     * Css class for detecting tree nodes.
     *
     * @var string
     */
    protected $nodeSelector = 'li[data-id]';

    /**
     * Css class for detecting tree nodes.
     *
     * @var string
     */
    protected $checkedNodeSelector = 'li.jstree-checked[data-id]';

    /**
     * Css class for fetching node's name.
     *
     * @var string
     */
    protected $nodeName = 'a';

    /**
     * Array, which holds all selected elements paths.
     *
     * @var array
     */
    protected $checkedNodesPaths = [];

    /**
     * Returns structure of the jquerytree element.
     *
     * @return array
     */
    public function getStructure()
    {
        return $this->_getNodeContent($this, 'div[class*=jstree] > ul');
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
        $counter = 1;
        $nextNodeSelector = $parentCssClass . " > " . $this->nodeSelector . ":nth-of-type($counter)";
        $nodeArray = [];
        //Get list of all children nodes to work with
        $newNode = $node->find($nextNodeSelector);
        while ($newNode->isVisible()) {
            $nextCheckedNodeSelector = $parentCssClass . " > " . $this->checkedNodeSelector . ":nth-of-type($counter)";
            $nodesNames = $newNode->find($this->nodeName);
            $text = ltrim($nodesNames->getText());
            $childNodeSelector = $nextNodeSelector . $this->nodeCssClass;
            $nodesContents = $newNode->find($childNodeSelector);
            $subNodes = null;
            if ($nodesContents->isVisible()) {
                $subNodes = $this->_getNodeContent($nodesContents, $childNodeSelector);
            }
            $nodeArray[] = [
                'name' => $text,
                'isChecked' => $node->find($nextCheckedNodeSelector)->isVisible() ? true : false,
                'element' => $newNode,
                'subnodes' => $subNodes,
            ];
            ++$counter;
            $nextNodeSelector = $parentCssClass . " > " . $this->nodeSelector . ":nth-of-type($counter)";
            $newNode = $node->find($nextNodeSelector);
        }
        return $nodeArray;
    }

    /**
     * Retrieve array of checked nodes from structure array.
     *
     * @param array $structure
     * @return array|null
     */
    protected function getCheckedNodes($structure)
    {
        $pathArray = [];
        if ($structure['isChecked'] == true) {
            array_push($pathArray, $structure['name']);
            if (is_array($structure['subnodes'])) {
                foreach ($structure['subnodes'] as $node) {
                    array_push($pathArray, $this->getCheckedNodes($node));
                }
            }
            return $pathArray;
        }
        return null;
    }

    /**
     * Method for recursive walk array of checked elements.
     * If element haven't subnodes, adds element's path to $checkedNodesPaths
     *
     * @param array $pathArray
     * @param string $rootPath
     * @return string
     */
    protected function getPathFromArray($pathArray, $rootPath = '')
    {
        $path = '';
        $rootPath = $rootPath == '' ? $pathArray[0] : $rootPath . '/' . $pathArray[0];
        if (count($pathArray) > 1) {
            for ($counter = 1; $counter < count($pathArray); $counter++) {
                $path .= $this->getPathFromArray($pathArray[$counter], $rootPath);
            }
        } else {
            $path = $rootPath;
            $this->checkedNodesPaths[] = $path;
        }
        return $path;
    }

    /**
     * Returns array of paths of all selected elements.
     *
     * @return array
     */
    public function getValue()
    {
        $pathsArray = [];
        $structure = $this->getStructure();
        foreach ($structure as $structureChunk) {
            $pathsArray[] = $this->getCheckedNodes($structureChunk);
        }
        foreach ($pathsArray as $pathArray) {
            $this->getPathFromArray($pathArray);
        }
        return array_filter($this->checkedNodesPaths);
    }
}
