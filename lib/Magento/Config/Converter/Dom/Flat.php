<?php
/**
 * Converter that dom to array converting all attributes to general array items.
 * Examlpe:
 * <node attr="val">
 *     <subnode>val2<subnode>
 * </node>
 *
 * is converted to
 *
 * array(
 *     'node' => array(
 *         'attr' => 'wal',
 *         'subnode' => 'val2'
 *     )
 * )
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Config\Converter\Dom;

class Flat implements \Magento\Config\ConverterInterface
{
    /**
     * Node identifier attributes
     *
     * @var array
     */
    protected $_idAttributes;

    /**
     * @param $idAttributes
     */
    public function __construct($idAttributes)
    {
        $this->_idAttributes = $idAttributes;
    }

    /**
     * Convert dom node tree to array
     *
     * @param \DOMNode $source
     * @param string $path
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function convert($source, $path = '')
    {
        $nodeListData = array();

        /** @var $node \DOMNode */
        foreach ($source->childNodes as $node) {
            if ($node->nodeType == XML_ELEMENT_NODE) {
                $nodeData = array();
                /** @var $attribute \DOMNode */
                foreach ($node->attributes as $attribute) {
                    if ($attribute->nodeType == XML_ATTRIBUTE_NODE) {
                        $nodeData[$attribute->nodeName] = $attribute->nodeValue;
                    }
                }
                $fullPath = $path . '/' . $node->nodeName;
                $childrenData = $this->convert($node, $fullPath);

                if (is_array($childrenData)) {
                    $nodeData = array_merge($nodeData, $childrenData);
                    if (!count($nodeData)) {
                        $nodeListData[$node->nodeName] = '';
                    } else if (isset($this->_idAttributes[$fullPath])) {
                        $nodeListData[$node->nodeName][$nodeData[$this->_idAttributes[$fullPath]]] = $nodeData;
                    } else {
                        $nodeListData[$node->nodeName] = $nodeData;
                    }
                } else {
                    if (count($nodeData)) {
                        $nodeData['value'] = $childrenData;
                    } else {
                        $nodeData = $childrenData;
                    }
                    $nodeListData[$node->nodeName] = $nodeData;
                }
            } elseif ($node->nodeType == XML_CDATA_SECTION_NODE
                || ($node->nodeType == XML_TEXT_NODE && trim($node->nodeValue) != '')
            ) {
                return (string) $node->nodeValue;
            }
        }
        return $nodeListData;
    }
}
