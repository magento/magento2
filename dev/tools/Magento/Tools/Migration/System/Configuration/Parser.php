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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * System configuration migration parser
 */
namespace Magento\Tools\Migration\System\Configuration;

class Parser
{
    /**
     * Parse dom document
     *
     * @param \DOMDocument $dom
     * @return array
     */
    public function parse(\DOMDocument $dom)
    {
        $result = array();
        if ($dom->hasChildNodes()) {
            foreach ($dom->childNodes as $child) {
                if (XML_COMMENT_NODE == $child->nodeType) {
                    $result['comment'] = $child->nodeValue;
                } else if (XML_ELEMENT_NODE == $child->nodeType && 'config' == $child->nodeName) {
                    $result = array_merge($result, $this->_parseNode($child));
                }
            }
        }
        return $result;
    }

    /**
     * Parse dom node
     *
     * @param \DOMNode $node
     * @return array
     */
    protected function _parseNode(\DOMNode $node)
    {
        $result = array();
        if (false === $node->hasChildNodes()) {
            $result = $this->_getSimpleNodeValue($node);
        } else {
            foreach ($node->childNodes as $childNode) {
                $sameNodesCount = $this->_getSameNodesCount(
                    $node->getElementsByTagName($childNode->nodeName),
                    $childNode
                );
                /** @var array $nodeValue  */
                $nodeValue = $this->_parseNode($childNode);

                $siblingKey = $this->_getSiblingKey($childNode);

                if ($siblingKey !== 0) {
                    $nodeValue = isset(
                        $nodeValue[$childNode->nodeName]
                    ) ? $nodeValue[$childNode->nodeName] : $nodeValue;
                } elseif (empty($nodeValue)) {
                    continue;
                }

                // how many of these child nodes do we have?
                if ($sameNodesCount > 1) {
                    // more than 1 child - make numeric array
                    $result[$siblingKey][] = $nodeValue;
                } else {
                    $result[$siblingKey] = $nodeValue;
                }
            }
            // if the child is <foo>bar</foo>, the result will be array(bar)
            // make the result just 'bar'
            if (count($result) == 1 && isset($result[0])) {
                $result = current($result);
            }
        }

        $attributes = $this->_parseNodeAttributes($node);
        $result = array_merge($result, $attributes);
        return $result;
    }

    /**
     * Get sibling key
     *
     * @param \DOMNode $childNode
     * @return int|string
     */
    protected function _getSiblingKey($childNode)
    {
        return $childNode->nodeName[0] == '#' ? 0 : $childNode->nodeName;
    }

    /**
     * Get count of the same nodes
     *
     * @param \DOMNodeList $childNodeList
     * @param \DOMNode $childNode
     * @return int
     */
    protected function _getSameNodesCount(\DOMNodeList $childNodeList, \DOMNode $childNode)
    {
        $childCount = 0;
        foreach ($childNodeList as $oNode) {
            if ($oNode->parentNode->isSameNode($childNode->parentNode)) {
                $childCount++;
            }
        }
        return $childCount;
    }

    /**
     * Get value of node without child nodes
     *
     * @param \DOMNode $node
     * @return array
     */
    protected function _getSimpleNodeValue(\DOMNode $node)
    {
        return trim($node->nodeValue) !== '' ? array($node->nodeName => $node->nodeValue) : array();
    }

    /**
     * Parse node attributes
     *
     * @param \DOMNode $node
     * @return array
     */
    protected function _parseNodeAttributes(\DOMNode $node)
    {
        $result = array();
        $attributes = array();
        if ($node->hasAttributes()) {
            foreach ($node->attributes as $oAttrNode) {
                $attributes[$oAttrNode->nodeName] = $oAttrNode->nodeValue;
            }
        }

        if (count($attributes)) {
            $result = array('@attributes' => $attributes);
        }
        return $result;
    }
}
