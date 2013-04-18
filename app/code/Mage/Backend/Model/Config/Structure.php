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
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * System configuration structure
 */
class Mage_Backend_Model_Config_Structure implements Mage_Backend_Model_Config_Structure_SearchInterface
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
     * @var Mage_Backend_Model_Config_Structure_Element_Iterator_Tab
     */
    protected $_tabIterator;

    /**
     * Pool of config element flyweight objects
     *
     * @var Mage_Backend_Model_Config_Structure_Element_FlyweightFactory
     */
    protected $_flyweightFactory;

    /**
     * Provider of current config scope
     *
     * @var Mage_Backend_Model_Config_ScopeDefiner
     */
    protected $_scopeDefiner;

    /**
     * List of cached elements
     *
     * @var Mage_Backend_Model_Config_Structure_ElementInterface[]
     */
    protected $_elements;

    /**
     * @param Mage_Backend_Model_Config_Structure_Reader $structureReader
     * @param Mage_Backend_Model_Config_Structure_Element_Iterator_Tab $tabIterator
     * @param Mage_Backend_Model_Config_Structure_Element_FlyweightFactory $flyweightFactory
     * @param Mage_Backend_Model_Config_ScopeDefiner $scopeDefiner
     */
    public function __construct(
        Mage_Backend_Model_Config_Structure_Reader $structureReader,
        Mage_Backend_Model_Config_Structure_Element_Iterator_Tab $tabIterator,
        Mage_Backend_Model_Config_Structure_Element_FlyweightFactory $flyweightFactory,
        Mage_Backend_Model_Config_ScopeDefiner $scopeDefiner
    ) {
        $this->_data = $structureReader->getData();
        $this->_tabIterator = $tabIterator;
        $this->_flyweightFactory = $flyweightFactory;
        $this->_scopeDefiner = $scopeDefiner;
    }

    /**
     * Retrieve tab iterator
     *
     * @return Mage_Backend_Model_Config_Structure_Element_Iterator
     */
    public function getTabs()
    {
        foreach ($this->_data['sections'] as $sectionId => $section) {
            if (isset($section['tab']) && $section['tab']) {
                $this->_data['tabs'][$section['tab']]['children'][$sectionId] = $section;
            }
        }
        $this->_tabIterator->setElements($this->_data['tabs'], $this->_scopeDefiner->getScope());
        return $this->_tabIterator;
    }

    /**
     * Find element by path
     *
     * @param string $path
     * @return Mage_Backend_Model_Config_Structure_ElementInterface|null
     */
    public function getElement($path)
    {
        return $this->getElementByPathParts(explode('/', $path));
    }

    /**
     * Retrieve first available section in config structure
     *
     * @return Mage_Backend_Model_Config_Structure_ElementInterface
     */
    public function getFirstSection()
    {
        $tabs = $this->getTabs();
        $tabs->rewind();
        /** @var $tab Mage_Backend_Model_Config_Structure_Element_Tab */
        $tab = $tabs->current();
        $tab->getChildren()->rewind();
        return $tab->getChildren()->current();
    }

    /**
     * Find element by path parts
     *
     * @param array $pathParts
     * @return Mage_Backend_Model_Config_Structure_ElementInterface|null
     */
    public function getElementByPathParts(array $pathParts)
    {
        $path = implode('_', $pathParts);
        if (isset($this->_elements[$path])) {
            return $this->_elements[$path];
        }
        $children = $this->_data['sections'];
        $child = array();
        foreach ($pathParts as $pathPart) {
            if (array_key_exists($pathPart, $children)) {
                $child = $children[$pathPart];
                $children = array_key_exists('children', $child) ? $child['children'] : array();
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
     * @param array $pathParts
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
        return array('id' => $elementId, 'path' => implode('/', $pathParts), '_elementType' => $elementType);
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
        $result = array();
        foreach ($this->_data['sections'] as $section) {
            if (!isset($section['children'])) {
                continue;
            }
            foreach ($section['children'] as $group) {
                if (isset($group['children'])) {
                    $path = $section['id'] . '/' . $group['id'];
                    $result = array_merge($result, $this->_getGroupFieldPathsByAttribute(
                        $group['children'], $path, $attributeName, $attributeValue
                    ));
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
        $result = array();
        foreach ($fields as $field) {
            if (isset($field['children'])) {
                $result += $this->_getGroupFieldPathsByAttribute(
                    $field['children'], $parentPath . '/' . $field['id'], $attributeName, $attributeValue
                );
            } else if (isset($field[$attributeName]) && $field[$attributeName] == $attributeValue) {
                $result[] = $parentPath . '/' . $field['id'];
            }
        }
        return $result;
    }
}
