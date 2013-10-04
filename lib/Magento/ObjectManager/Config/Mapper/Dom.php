<?php
/**
 * ObjectManager configuration DOM mapper
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ObjectManager\Config\Mapper;

class Dom implements \Magento\Config\ConverterInterface
{
    /**
     * Convert configuration in DOM format to assoc array that can be used by object manager
     *
     * @param mixed $config
     * @return array
     * @throws \Exception
     * @todo this method has high cyclomatic complexity in order to avoid performance issues
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function convert($config)
    {
        $output = array();
        /** @var \DOMNode $node */
        foreach ($config->documentElement->childNodes as $node) {
            if ($node->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            switch ($node->nodeName) {
                case 'preference':
                    $output['preferences'][$node->attributes->getNamedItem('for')->nodeValue] = $node->attributes
                        ->getNamedItem('type')
                        ->nodeValue;
                    break;
                case 'type':
                case 'virtualType':
                    $typeData = array();
                    $typeNodeAttributes = $node->attributes;
                    $typeNodeShared = $typeNodeAttributes->getNamedItem('shared');
                    if (!is_null($typeNodeShared)) {
                        $typeData['shared'] = ($typeNodeShared->nodeValue == 'false') ? false : true;
                    }
                    if ($node->nodeName == 'virtualType') {
                        $attributeType = $typeNodeAttributes->getNamedItem('type');
                        // attribute type is required for virtual type only in merged configuration
                        if (!is_null($attributeType)) {
                            $typeData['type'] = $attributeType->nodeValue;
                        }
                    }
                    $typeParameters = array();
                    $typePlugins = array();
                    /** @var \DOMNode $typeChildNode */
                    foreach ($node->childNodes as $typeChildNode) {
                        if ($typeChildNode->nodeType != XML_ELEMENT_NODE) {
                            continue;
                        }
                        switch ($typeChildNode->nodeName) {
                            case 'param':
                                $paramData = array();
                                /** @var \DOMNode $paramChildNode */
                                foreach ($typeChildNode->childNodes as $paramChildNode) {
                                    if ($paramChildNode->nodeType != XML_ELEMENT_NODE) {
                                        continue;
                                    }
                                    switch ($paramChildNode->nodeName) {
                                        case 'instance':
                                            $instanceSharedNode = $paramChildNode->attributes->getNamedItem('shared');
                                            $paramData = array(
                                                'instance' => $paramChildNode->attributes
                                                    ->getNamedItem('type')
                                                    ->nodeValue,
                                            );
                                            if ($instanceSharedNode) {
                                                $paramData['shared'] = ($instanceSharedNode->nodeValue == 'false')
                                                    ? false : true;
                                            }
                                            break;
                                        case 'value':
                                            $paramData = $this->_processValueNode($paramChildNode);
                                            break;
                                        default:
                                            throw new \Exception(
                                                "Invalid application config. Unknown node: {$paramChildNode->nodeName}."
                                            );
                                    }
                                }
                                $typeParameters[$typeChildNode->attributes->getNamedItem('name')->nodeValue]
                                    = $paramData;
                                break;
                            case 'plugin':
                                $pluginAttributes = $typeChildNode->attributes;
                                $pluginDisabledNode = $pluginAttributes->getNamedItem('disabled');
                                $pluginSortOrderNode = $pluginAttributes->getNamedItem('sortOrder');
                                $pluginTypeNode = $pluginAttributes->getNamedItem('type');
                                $pluginData = array(
                                    'sortOrder' => ($pluginSortOrderNode) ? (int)$pluginSortOrderNode->nodeValue : 0,
                                );
                                if ($pluginDisabledNode) {
                                    $pluginData['disabled'] = ($pluginDisabledNode->nodeValue == 'true') ? true : false;
                                }
                                if ($pluginTypeNode) {
                                    $pluginData['instance'] = $pluginTypeNode->nodeValue;
                                }
                                $typePlugins[$pluginAttributes->getNamedItem('name')->nodeValue] = $pluginData;
                                break;
                            default:
                                throw new \Exception(
                                    "Invalid application config. Unknown node: {$typeChildNode->nodeName}."
                                );
                        }
                    }

                    $typeData['parameters'] = $typeParameters;
                    if (!empty($typePlugins)) {
                        $typeData['plugins'] = $typePlugins;
                    }
                    $output[$typeNodeAttributes->getNamedItem('name')->nodeValue] = $typeData;
                    break;
                default:
                    throw new \Exception("Invalid application config. Unknown node: {$node->nodeName}.");
            }
        }

        return $output;
    }

    /**
     * Retrieve value of the given node
     * Treat all child nodes as an assoc array
     * @todo this method has high cyclomatic complexity in order to avoid performance issues
     * @param \DOMNode $valueNode
     * @return array|string
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _processValueNode(\DOMNode $valueNode)
    {
        $output = array();
        $childNodesCount = $valueNode->childNodes->length;
        $valueNodeType = $valueNode->attributes->getNamedItem('type');
        if ($valueNodeType && 'null' == $valueNodeType->nodeValue) {
            return null;
        }
        
        /** @var \DOMNode $node */
        foreach ($valueNode->childNodes as $node) {
            if ($node->nodeType == XML_ELEMENT_NODE) {
                $nodeType = $node->attributes->getNamedItem('type');
                if ($nodeType && 'null' == $nodeType->nodeValue) {
                    $output[$node->nodeName] = null;
                } else {
                    $output[$node->nodeName] = $this->_processValueNode($node);
                }
            } elseif (($node->nodeType == XML_TEXT_NODE || $node->nodeType == XML_CDATA_SECTION_NODE)
                && $childNodesCount == 1
            ) {
                // process DomText or \DOMCharacterData node only if it is a single child of its parent
                $output = trim($node->nodeValue);
                if ($valueNodeType) {
                    switch ($valueNodeType->nodeValue) {
                        case 'const':
                            $output = constant($output);
                            break;
                        case 'argument':
                            $output = array('argument' => constant($output));
                            break;
                        case 'bool':
                            $output = strtolower($output) == 'true' || $output == '1';
                            break;
                        case 'int':
                            if (!preg_match('/^[0-9]*$/', $output)) {
                                throw new \InvalidArgumentException('Invalid integer value');
                            }
                            $output = (int)$output;
                            break;
                        case 'string':
                            $pattern = $valueNode->attributes->getNamedItem('pattern')->nodeValue;
                            if (!preg_match('/^' . $pattern . '$/', $output)) {
                                throw new \InvalidArgumentException('Invalid string value format');
                            }
                            break;
                        default:
                            throw new \InvalidArgumentException('Unknown parameter type');
                    }
                }
            }
        }
        return $output;
    }
}
