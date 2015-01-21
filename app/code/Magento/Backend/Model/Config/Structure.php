<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System configuration structure
 */
namespace Magento\Backend\Model\Config;

class Structure implements \Magento\Backend\Model\Config\Structure\SearchInterface
{
    /**
     * Key that contains field type in structure array
     */
    const TYPE_KEY = '_elementType';

    /**
     * Configuration structure represented as tree
     *
     * @var array
     */
    protected $_data;

    /**
     * Config tab iterator
     *
     * @var \Magento\Backend\Model\Config\Structure\Element\Iterator\Tab
     */
    protected $_tabIterator;

    /**
     * Pool of config element flyweight objects
     *
     * @var \Magento\Backend\Model\Config\Structure\Element\FlyweightFactory
     */
    protected $_flyweightFactory;

    /**
     * Provider of current config scope
     *
     * @var ScopeDefiner
     */
    protected $_scopeDefiner;

    /**
     * List of cached elements
     *
     * @var \Magento\Backend\Model\Config\Structure\ElementInterface[]
     */
    protected $_elements;

    /**
     * @param \Magento\Backend\Model\Config\Structure\Data $structureData
     * @param \Magento\Backend\Model\Config\Structure\Element\Iterator\Tab $tabIterator
     * @param \Magento\Backend\Model\Config\Structure\Element\FlyweightFactory $flyweightFactory
     * @param ScopeDefiner $scopeDefiner
     */
    public function __construct(
        \Magento\Backend\Model\Config\Structure\Data $structureData,
        \Magento\Backend\Model\Config\Structure\Element\Iterator\Tab $tabIterator,
        \Magento\Backend\Model\Config\Structure\Element\FlyweightFactory $flyweightFactory,
        ScopeDefiner $scopeDefiner
    ) {
        $this->_data = $structureData->get();
        $this->_tabIterator = $tabIterator;
        $this->_flyweightFactory = $flyweightFactory;
        $this->_scopeDefiner = $scopeDefiner;
    }

    /**
     * Retrieve tab iterator
     *
     * @return \Magento\Backend\Model\Config\Structure\Element\Iterator
     */
    public function getTabs()
    {
        if (isset($this->_data['sections'])) {
            foreach ($this->_data['sections'] as $sectionId => $section) {
                if (isset($section['tab']) && $section['tab']) {
                    $this->_data['tabs'][$section['tab']]['children'][$sectionId] = $section;
                }
            }
            $this->_tabIterator->setElements($this->_data['tabs'], $this->_scopeDefiner->getScope());
        }
        return $this->_tabIterator;
    }

    /**
     * Find element by path
     *
     * @param string $path
     * @return \Magento\Backend\Model\Config\Structure\ElementInterface|null
     */
    public function getElement($path)
    {
        return $this->getElementByPathParts(explode('/', $path));
    }

    /**
     * Retrieve first available section in config structure
     *
     * @return \Magento\Backend\Model\Config\Structure\ElementInterface
     */
    public function getFirstSection()
    {
        $tabs = $this->getTabs();
        $tabs->rewind();
        /** @var $tab \Magento\Backend\Model\Config\Structure\Element\Tab */
        $tab = $tabs->current();
        $tab->getChildren()->rewind();
        return $tab->getChildren()->current();
    }

    /**
     * Find element by path parts
     *
     * @param string[] $pathParts
     * @return \Magento\Backend\Model\Config\Structure\ElementInterface|null
     */
    public function getElementByPathParts(array $pathParts)
    {
        $path = implode('_', $pathParts);
        if (isset($this->_elements[$path])) {
            return $this->_elements[$path];
        }
        $children = [];
        if ($this->_data) {
            $children = $this->_data['sections'];
        }
        $child = [];
        foreach ($pathParts as $pathPart) {
            if ($children && (array_key_exists($pathPart, $children))) {
                $child = $children[$pathPart];
                $children = array_key_exists('children', $child) ? $child['children'] : [];
            } else {
                $child = $this->_createEmptyElement($pathParts);
                break;
            }
        }
        $this->_elements[$path] = $this->_flyweightFactory->create($child['_elementType']);
        $this->_elements[$path]->setData($child, $this->_scopeDefiner->getScope());
        return $this->_elements[$path];
    }

    /**
     * Create empty element data
     *
     * @param string[] $pathParts
     * @return array
     */
    protected function _createEmptyElement(array $pathParts)
    {
        switch (count($pathParts)) {
            case 1:
                $elementType = 'section';
                break;
            case 2:
                $elementType = 'group';
                break;
            default:
                $elementType = 'field';
        }
        $elementId = array_pop($pathParts);
        return ['id' => $elementId, 'path' => implode('/', $pathParts), '_elementType' => $elementType];
    }

    /**
     * Retrieve paths of fields that have provided attributes with provided values
     *
     * @param string $attributeName
     * @param mixed $attributeValue
     * @return array
     */
    public function getFieldPathsByAttribute($attributeName, $attributeValue)
    {
        $result = [];
        foreach ($this->_data['sections'] as $section) {
            if (!isset($section['children'])) {
                continue;
            }
            foreach ($section['children'] as $group) {
                if (isset($group['children'])) {
                    $path = $section['id'] . '/' . $group['id'];
                    $result = array_merge(
                        $result,
                        $this->_getGroupFieldPathsByAttribute(
                            $group['children'],
                            $path,
                            $attributeName,
                            $attributeValue
                        )
                    );
                }
            }
        }
        return $result;
    }

    /**
     * Find group fields with specified attribute and attribute value
     *
     * @param array $fields
     * @param string $parentPath
     * @param string $attributeName
     * @param mixed $attributeValue
     * @return array
     */
    protected function _getGroupFieldPathsByAttribute(array $fields, $parentPath, $attributeName, $attributeValue)
    {
        $result = [];
        foreach ($fields as $field) {
            if (isset($field['children'])) {
                $result += $this->_getGroupFieldPathsByAttribute(
                    $field['children'],
                    $parentPath . '/' . $field['id'],
                    $attributeName,
                    $attributeValue
                );
            } elseif (isset($field[$attributeName]) && $field[$attributeName] == $attributeValue) {
                $result[] = $parentPath . '/' . $field['id'];
            }
        }
        return $result;
    }
}
