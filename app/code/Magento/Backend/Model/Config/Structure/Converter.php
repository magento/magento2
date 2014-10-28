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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model\Config\Structure;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * @var \Magento\Backend\Model\Config\Structure\Mapper\Factory
     */
    protected $_mapperFactory;

    /**
     * Mapper type list
     *
     * @var string[]
     */
    protected $_mapperList = array(
        \Magento\Backend\Model\Config\Structure\Mapper\Factory::MAPPER_EXTENDS,
        \Magento\Backend\Model\Config\Structure\Mapper\Factory::MAPPER_PATH,
        \Magento\Backend\Model\Config\Structure\Mapper\Factory::MAPPER_DEPENDENCIES,
        \Magento\Backend\Model\Config\Structure\Mapper\Factory::MAPPER_ATTRIBUTE_INHERITANCE,
        \Magento\Backend\Model\Config\Structure\Mapper\Factory::MAPPER_IGNORE,
        \Magento\Backend\Model\Config\Structure\Mapper\Factory::MAPPER_SORTING
    );

    /**
     * Map of single=>plural sub-node names per node
     *
     * E.G. first element makes all 'tab' nodes be renamed to 'tabs' in system node.
     *
     * @var array
     */
    protected $_nameMap = array(
        'system' => array('tab' => 'tabs', 'section' => 'sections'),
        'section' => array('group' => 'children'),
        'group' => array('field' => 'children', 'group' => 'children'),
        'depends' => array('field' => 'fields')
    );

    /**
     * @param \Magento\Backend\Model\Config\Structure\Mapper\Factory $mapperFactory
     */
    public function __construct(\Magento\Backend\Model\Config\Structure\Mapper\Factory $mapperFactory)
    {
        $this->_mapperFactory = $mapperFactory;
    }

    /**
     * Convert dom document
     *
     * @param \DOMNode $source
     * @return array
     */
    public function convert($source)
    {
        $result = $this->_convertDOMDocument($source);

        foreach ($this->_mapperList as $type) {
            /** @var $mapper MapperInterface */
            $mapper = $this->_mapperFactory->create($type);
            $result = $mapper->map($result);
        }

        return $result;
    }

    /**
     * Retrieve \DOMDocument as array
     *
     * @param \DOMNode $root
     * @return array|null
     */
    protected function _convertDOMDocument(\DOMNode $root)
    {
        $result = $this->_processAttributes($root);

        $children = $root->childNodes;

        $processedSubLists = array();
        for ($i = 0; $i < $children->length; $i++) {
            $child = $children->item($i);
            $childName = $child->nodeName;
            $convertedChild = array();

            switch ($child->nodeType) {
                case XML_COMMENT_NODE:
                    continue 2;
                    break;

                case XML_TEXT_NODE:
                    if ($children->length && trim($child->nodeValue, "\n ") === '') {
                        continue 2;
                    }
                    $childName = 'value';
                    $convertedChild = $child->nodeValue;
                    break;

                case XML_CDATA_SECTION_NODE:
                    $childName = 'value';
                    $convertedChild = $child->nodeValue;
                    break;

                default:
                    /** @var $child \DOMElement */
                    if ($childName == 'attribute') {
                        $childName = $child->getAttribute('type');
                    }
                    $convertedChild = $this->_convertDOMDocument($child);
                    break;
            }

            if (array_key_exists(
                $root->nodeName,
                $this->_nameMap
            ) && array_key_exists(
                $child->nodeName,
                $this->_nameMap[$root->nodeName]
            )
            ) {
                $childName = $this->_nameMap[$root->nodeName][$child->nodeName];
                $processedSubLists[] = $childName;
                $convertedChild['_elementType'] = $child->nodeName;
            }

            if (in_array($childName, $processedSubLists)) {
                $result = $this->_addProcessedNode($convertedChild, $result, $childName);
            } else if (array_key_exists($childName, $result)) {
                $result[$childName] = array($result[$childName], $convertedChild);
                $processedSubLists[] = $childName;
            } else {
                $result[$childName] = $convertedChild;
            }
        }

        if (count($result) == 1 && array_key_exists('value', $result)) {
            $result = $result['value'];
        }
        if ($result == array()) {
            $result = null;
        }

        return $result;
    }

    /**
     * Add converted child with processed name
     *
     * @param array $convertedChild
     * @param array $result
     * @param string $childName
     * @return array
     */
    protected function _addProcessedNode($convertedChild, $result, $childName)
    {
        if (is_array($convertedChild) && array_key_exists('id', $convertedChild)) {
            $result[$childName][$convertedChild['id']] = $convertedChild;
        } else {
            $result[$childName][] = $convertedChild;
        }
        return $result;
    }

    /**
     * Process element attributes
     * 
     * @param \DOMNode $root
     * @return array
     */
    protected function _processAttributes(\DOMNode $root)
    {
        $result = array();

        if ($root->hasAttributes()) {
            $attributes = $root->attributes;
            foreach ($attributes as $attribute) {
                if ($root->nodeName == 'attribute' && $attribute->name == 'type') {
                    continue;
                }
                $result[$attribute->name] = $attribute->value;
            }
            return $result;
        }
        return $result;
    }
}
