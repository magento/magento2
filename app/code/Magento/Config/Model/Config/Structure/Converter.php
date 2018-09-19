<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure;

/**
 * @api
 * @since 100.0.2
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * @var \Magento\Config\Model\Config\Structure\Mapper\Factory
     */
    protected $_mapperFactory;

    /**
     * Mapper type list
     *
     * @var string[]
     */
    protected $_mapperList = [
        \Magento\Config\Model\Config\Structure\Mapper\Factory::MAPPER_EXTENDS,
        \Magento\Config\Model\Config\Structure\Mapper\Factory::MAPPER_PATH,
        \Magento\Config\Model\Config\Structure\Mapper\Factory::MAPPER_DEPENDENCIES,
        \Magento\Config\Model\Config\Structure\Mapper\Factory::MAPPER_ATTRIBUTE_INHERITANCE,
        \Magento\Config\Model\Config\Structure\Mapper\Factory::MAPPER_IGNORE,
        \Magento\Config\Model\Config\Structure\Mapper\Factory::MAPPER_SORTING,
    ];

    /**
     * Map of single=>plural sub-node names per node
     *
     * E.G. first element makes all 'tab' nodes be renamed to 'tabs' in system node.
     *
     * @var array
     */
    protected $_nameMap = [
        'system' => ['tab' => 'tabs', 'section' => 'sections'],
        'section' => ['group' => 'children'],
        'group' => ['field' => 'children', 'group' => 'children'],
        'depends' => ['field' => 'fields'],
    ];

    /**
     * @param \Magento\Config\Model\Config\Structure\Mapper\Factory $mapperFactory
     */
    public function __construct(\Magento\Config\Model\Config\Structure\Mapper\Factory $mapperFactory)
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _convertDOMDocument(\DOMNode $root)
    {
        $result = $this->_processAttributes($root);

        $children = $root->childNodes;

        $processedSubLists = [];
        for ($i = 0; $i < $children->length; $i++) {
            $child = $children->item($i);
            $childName = $child->nodeName;
            $convertedChild = [];

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
            } elseif (array_key_exists($childName, $result)) {
                $result[$childName] = [$result[$childName], $convertedChild];
                $processedSubLists[] = $childName;
            } else {
                $result[$childName] = $convertedChild;
            }
        }

        if (count($result) == 1 && array_key_exists('value', $result)) {
            $result = $result['value'];
        }
        if ($result == []) {
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
        $result = [];

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
