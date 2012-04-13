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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Layout structure model
 *
 * @category   Mage
 * @package    Mage_Core
 */
class Mage_Core_Model_Layout_Structure
{
    /**
     * Available element types
     */
    const ELEMENT_TYPE_BLOCK = 'block';
    const ELEMENT_TYPE_CONTAINER = 'container';

    /**
     * Prefix for temporary names of elements
     */
    const TMP_NAME_PREFIX = 'ANONYMOUS_';

    /**
     * Page structure as DOM document
     *
     * @var DOMDocument
     */
    protected $_dom;

    /**
     * Xpath object
     *
     * @var DOMXPath
     */
    protected $_xpath;

    /**
     * Increment for temporary names of elements
     *
     * @var int
     */
    protected $_nameIncrement = 0;

    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        $this->_dom = new DOMDocument();
        $this->_dom->formatOutput = true;
        $this->_dom->loadXML("<layout/>");
        $this->_xpath = new DOMXPath($this->_dom);
    }

    /**
     * Get parent element by name
     *
     * @param string $name
     * @return bool|string
     */
    public function getParentName($name)
    {
        $elements = $this->_getAllElementsByName($name);
        $length = $elements->length;
        if ($length) {
            if ($length > 1) {
                Mage::logException(new Magento_Exception("Too many parents found for element [$name]: " . $length));
            }
            $element = $elements->item($length - 1);
            return $element->parentNode->getAttribute('name');
        }
        return false;
    }

    /**
     * Get sorted list of child aliases by parent name
     *
     * @param string $parentName
     * @return array
     */
    public function getChildNames($parentName)
    {
        $children = array();
        /** @var $child DOMElement */
        foreach ($this->_findByXpath("//element[@name='$parentName']/element") as $child) {
            $children[] = $child->getAttribute('name');
        }
        return $children;
    }

    /**
     * Move node to necessary parent node. If node doesn't exist, create it
     *
     * @param string $parentName
     * @param string $elementName
     * @param string $alias
     * @return Mage_Core_Model_Layout_Structure
     * @throws InvalidArgumentException
     */
    public function setChild($parentName, $elementName, $alias)
    {
        if (!$elementName) {
            throw new InvalidArgumentException('$elementName should be non-empty string');
        }
        if (empty($alias)) {
            $alias = $elementName;
        }
        $element = $this->_getElementByName($elementName);
        if (!$element) {
            $this->insertBlock($parentName, $elementName, $alias);
        } else {
            $element->setAttribute('alias', $alias);
            $this->_move($element, $parentName);
        }

        return $this;
    }

    /**
     * Sets alias for an element with specified name
     *
     * @param string $name
     * @param string $alias
     * @return Mage_Core_Model_Layout_Structure|string
     */
    public function setElementAlias($name, $alias)
    {
        $this->_setElementAttribute($name, 'alias', $alias);
        return $this;
    }

    /**
     * Get element alias by name
     *
     * @param string $name
     * @return string
     */
    public function getElementAlias($name)
    {
        return $this->getElementAttribute($name, 'alias');
    }

    /**
     * Change element's name
     *
     * @param string $oldName
     * @param string $newName
     * @return Mage_Core_Model_Layout_Structure|string
     */
    public function renameElement($oldName, $newName)
    {
        if (!empty($newName)) {
            $this->_setElementAttribute($oldName, 'name', $newName);
        }
        return $this;
    }

    /**
     * Set element attribute
     *
     * @param string $name
     * @param string $attribute
     * @return string
     */
    public function getElementAttribute($name, $attribute)
    {
        $element = $this->_getElementByName($name);
        if ($element && $element->hasAttribute($attribute)) {
            return $element->getAttribute($attribute);
        }

        return '';
    }

    /**
     * Set element attribute
     *
     * @param string $name
     * @param string $attribute
     * @param string $value
     * @return bool
     */
    protected function _setElementAttribute($name, $attribute, $value)
    {
        $element = $this->_getElementByName($name);
        if (!$element) {
            return false;
        }
        $element->setAttribute($attribute, $value);

        return true;
    }

    /**
     * Move child element to new parent
     *
     * @param string $childName
     * @param string $parent
     * @return Mage_Core_Model_Layout_Structure
     */
    public function move($childName, $parent)
    {
        $child = $this->_getElementByName($childName);
        if ($child) {
            $this->_move($child, $parent);
        }

        return $this;
    }

    /**
     * Remove child from parent element
     *
     * @param string $parentName
     * @param string $alias
     * @return Mage_Core_Model_Layout_Structure
     */
    public function unsetChild($parentName, $alias)
    {
        $parent = $this->_getElementByXpath("//element[@name='$parentName']");
        if ($parent) {
            $child = $this->_getElementByXpath("element[@alias='$alias']", $parent);
            if ($child) {
                $parent->removeChild($child);
            }
        }

        return $this;
    }

    /**
     * Remove elements with specified name from the structure
     *
     * @param string $name
     * @return Mage_Core_Model_Layout_Structure
     */
    public function unsetElement($name)
    {
        foreach ($this->_getAllElementsByName($name) as $element) {
            $element->parentNode->removeChild($element);
        }

        return $this;
    }

    /**
     * Get child name by parent name and alias
     *
     * @param string $parentName
     * @param string $alias
     * @return string|bool
     */
    public function getChildName($parentName, $alias)
    {
        $child = $this->_getChildElement($parentName, $alias);
        if (!$child) {
            return false;
        }
        return $child->getAttribute('name');
    }

    /**
     * Add new element to necessary position in the structure
     *
     * @param string $parentName
     * @param string $name
     * @param string $type
     * @param string $alias
     * @param string|null $sibling
     * @param bool $after
     * @param array $options
     * @return string|bool
     */
    public function insertElement($parentName, $name, $type, $alias = '', $sibling = null, $after = true,
        $options = array()
    ) {
        if (!in_array($type, array(self::ELEMENT_TYPE_BLOCK, self::ELEMENT_TYPE_CONTAINER))) {
            return false;
        }

        if (empty($name) || '.' === $name{0}) {
            $name = self::TMP_NAME_PREFIX . ($this->_nameIncrement++);
        }
        if ($alias == '') {
            $alias = $name;
        }

        $child = $this->_getTempOrNewNode($name);
        $child->setAttribute('type', $type);
        $child->setAttribute('alias', $alias);
        if ($sibling) {
            if ($after) {
                $attributeName = 'after';
            } else {
                $attributeName = 'before';
            }
            $child->setAttribute($attributeName, $sibling);
        }
        foreach ($options as $optName => $value) {
            $child->setAttribute($optName, $value);
        }

        $parentNode = $this->_findOrCreateParentNode($parentName);
        $this->_clearExistingChild($parentNode, $alias);

        $siblingNode = $this->_getSiblingElement($parentNode, $sibling, $after);
        $parentNode->insertBefore($child, $siblingNode);

        return $name;
    }

    /**
     * Gets temporary node with specified name, creates new node if it doesn't exist
     *
     * @param string $name
     * @return DOMElement|null
     */
    protected function _getTempOrNewNode($name)
    {
        $child = $this->_getElementByXpath("//element[not(@type) and @name='$name']");
        if (!$child) {
            if ($length = $this->_getAllElementsByName($name)->length) {
                Mage::logException(new Magento_Exception("Element with name [$name] already exists (" . $length . ')'));
            }
            $child = $this->_dom->createElement('element');
            $child->setAttribute('name', $name);
        }
        return $child;
    }

    /**
     * Gets parent node with specified name, creates new if it doesn't exist
     * If $parentNode is not specified, returns root document node
     *
     * @param string $parentName
     * @return bool|DOMElement|DOMNode
     */
    protected function _findOrCreateParentNode($parentName)
    {
        if ($parentName) {
            $parentNode = $this->_getElementByName($parentName);
            if (!$parentNode) {
                $parentNode = $this->_dom->createElement('element');
                $parentNode->setAttribute('name', $parentName);
                $this->_dom->appendChild($parentNode);
            }
        } else {
            $parentNode = $this->_dom->firstChild;
        }
        return $parentNode;
    }

    /**
     * Get sibling element based on $sibling and $after parameters
     *
     * @param DOMElement $parentNode
     * @param string|null $sibling
     * @param bool $after
     * @return DOMElement|null
     */
    protected function _getSiblingElement(DOMElement $parentNode, $sibling, $after)
    {
        if (!$sibling || !$parentNode->hasChildNodes()) {
            return null;
        }

        $siblingNode = null;
        if ($sibling != '-') {
            $siblingNode = $this->_getChildElement($parentNode->getAttribute('name'), $sibling);
        }
        if (!$siblingNode) {
            return $after ? null : $parentNode->firstChild;
        }

        return $after ? $siblingNode->nextSibling : $siblingNode;
    }

    /**
     * Remove existing child element
     *
     * @param DOMElement $parentNode
     * @param string $alias
     * @return bool
     */
    protected function _clearExistingChild(DOMElement $parentNode, $alias)
    {
        $existent = $this->_getElementByXpath("element[@alias='$alias']", $parentNode);
        if ($existent) {
            $parentNode->removeChild($existent);
            return true;
        }
        return false;
    }

    /**
     * Add new block to necessary position in the structure
     *
     * @param string $parentName
     * @param string $name
     * @param string $alias
     * @param string|null $sibling
     * @param bool $after
     * @param array $options
     * @return string|bool
     */
    public function insertBlock($parentName, $name, $alias = '', $sibling = null, $after = true, $options = array())
    {
        return $this->insertElement($parentName, $name, self::ELEMENT_TYPE_BLOCK, $alias, $sibling, $after, $options);
    }

    /**
     * Add new container to necessary position in the structure
     *
     * @param string $parentName
     * @param string $name
     * @param string $alias
     * @param string|null $sibling
     * @param bool $after
     * @param array $options
     * @return string|bool
     */
    public function insertContainer($parentName, $name, $alias = '', $sibling = null, $after = true, $options = array())
    {
        return $this->insertElement(
            $parentName, $name, self::ELEMENT_TYPE_CONTAINER, $alias, $sibling, $after, $options
        );
    }

    /**
     * Check if element with specified name exists in the structure
     *
     * @param string $name
     * @return bool
     */
    public function hasElement($name)
    {
        return $this->_getAllElementsByName($name)->length > 0;
    }

    /**
     * Get children count
     *
     * @param string $name
     * @return int
     */
    public function getChildrenCount($name)
    {
        return $this->_findByXpath("//element[@name='$name']/element")->length;
    }

    /**
     * Add element to parent group
     *
     * @param string $name
     * @param string $groupName
     * @return bool
     */
    public function addToParentGroup($name, $groupName)
    {
        $parentName = $this->getParentName($name);
        if (!$parentName) {
            return false;
        }
        $parentElement = $this->_getElementByName($parentName);
        if (!$parentElement
            || $this->_getElementByXpath("groups/group[@name='$groupName']/child[@name='$name']", $parentElement)) {
            return false;
        }

        $group = $this->_getElementByXpath("groups/group[@name='$groupName']", $parentElement);
        if (!$group) {
            $groups = $this->_getElementByXpath('groups', $parentElement);
            if (!$groups) {
                $groups = $this->_dom->createElement('groups');
                $parentElement->appendChild($groups);
            }
            $group = $this->_dom->createElement('group');
            $groups->appendChild($group);
            $group->setAttribute('name', $groupName);
        }

        $child = $this->_dom->createElement('child');
        $group->appendChild($child);
        $child->setAttribute('name', $name);

        return true;
    }

    /**
     * Get element names for specified group
     *
     * @param string $name Name of an element containing group
     * @param string $groupName
     * @return array
     */
    public function getGroupChildNames($name, $groupName)
    {
        $children = array();
        $elements = $this->_findByXpath("//element[@name='$name']/groups/group[@name='$groupName']/child");
        /** @var $element DOMElement */
        foreach ($elements as $element) {
            $children[] = $element->getAttribute('name');
        }

        return $children;
    }

    /**
     * Check if element with specified name is block
     *
     * @param string $name
     * @return bool
     */
    public function isBlock($name)
    {
        return $this->_findByXpath("//element[@name='$name' and @type='" . self::ELEMENT_TYPE_BLOCK. "']")->length > 0;
    }

    /**
     * Check if element with specified name is container
     *
     * @param string $name
     * @return bool
     */
    public function isContainer($name)
    {
        return $this->_findByXpath("//element[@name='$name' and @type='" . self::ELEMENT_TYPE_CONTAINER. "']")
            ->length > 0;
    }

    /**
     * Whether the specified element may be manipulated externally
     *
     * @param string $name
     * @return bool
     */
    public function isManipulationAllowed($name)
    {
        $element = $this->_getElementByName($name);
        $parent = isset($element->parentNode) ? $element->parentNode : null;
        if ($parent && self::ELEMENT_TYPE_CONTAINER == $parent->getAttribute('type')) {
            return true;
        }
        return false;
    }

    /**
     * Get child node from a parent
     *
     * @param string $parentName
     * @param string $alias
     * @return DOMElement|null
     */
    protected function _getChildElement($parentName, $alias)
    {
        if (!$alias) {
            return null;
        }
        return $this->_getElementByXpath("//element[@name='$parentName']/element[@alias='$alias']");
    }

    /**
     * Move element to new parent node
     *
     * @param DOMElement $element
     * @param string $newParent
     * @return Mage_Core_Model_Layout_Structure
     * @throws Magento_Exception
     */
    protected function _move($element, $newParent)
    {
        $parentNode = $this->_getElementByName($newParent);
        if (!$parentNode) {
            throw new Magento_Exception(
                "Can not move element [" . $element->getAttribute('name') . "]: parent is not found"
            );
        }

        $this->_clearExistingChild($parentNode, $element->getAttribute('alias'));
        $parentNode->appendChild($element);
        return $this;
    }

    /**
     * Get element by name
     *
     * @param string $name
     * @return DOMElement
     */
    protected function _getElementByName($name)
    {
        return $this->_getElementByXpath("//element[@name='$name']");
    }

    /**
     * @param string $name
     * @return DOMNodeList
     */
    protected function _getAllElementsByName($name)
    {
        return $this->_findByXpath("//element[@name='$name']");
    }

    /**
     * Find element(s) by xpath
     *
     * @param string $xpath
     * @param DOMElement $context
     * @return DOMNodeList
     */
    protected function _findByXpath($xpath, $context = null)
    {
        if (is_null($context)) {
            return $this->_xpath->query($xpath);
        }
        return $this->_xpath->query($xpath, $context);
    }

    /**
     * Get first element by xpath
     *
     * Gets element by xpath
     *
     * @param string $xpath
     * @param null|DOMElement $context
     * @return null|DOMElement
     */
    protected function _getElementByXpath($xpath, $context = null)
    {
        $elements = $this->_findByXpath($xpath, $context);
        if ($elements->length) {
            return $elements->item(0);
        } else {
            return null;
        }
    }

    /**
     * Sort elements based on their "after" and "before" elements
     *
     * @return Mage_Core_Model_Layout_Structure
     */
    public function sortElements()
    {
        $this->_sortChildren($this->_dom->firstChild);
        return $this;
    }

    /**
     * Recursively goes through all levels of dom tree and sorts elements within a level based on "after" and "before"
     * attributes
     *
     * @param DOMDocument $currentNode
     * @return Mage_Core_Model_Layout_Structure
     */
    protected function _sortChildren($currentNode)
    {
        /**
         * Important to put nodes in array, otherwise we can get unpredictable results with changing list
         * while iterating over it
         */
        $childNodes = array();
        foreach ($currentNode->childNodes as $node) {
            $childNodes[] = $node;
        }
        foreach ($childNodes as $node) {
            $this->_repositionNodeIfNeeded($node)
                ->_sortChildren($node);
        }
        return $this;
    }

    /**
     * Checks if node is marked with "before" or "after" attributes and repositions it to required place
     *
     * @param DOMElement $node
     * @return Mage_Core_Model_Layout_Structure
     */
    protected function _repositionNodeIfNeeded($node)
    {
        $before = $node->getAttribute('before');
        $after = $node->getAttribute('after');
        $siblingName = $before ?: $after;
        if (!$siblingName) {
            return $this;
        }

        // Choose a node to insert before. "Null" will transform insertBefore() into appendChild().
        $parentNode = $node->parentNode;
        $insertBefore = null;
        if ($siblingName === '-') {
            if ($before) {
                $insertBefore = $parentNode->firstChild;
            }
        } else {
            $element = $this->_getElementByXpath($parentNode->getNodePath() . "/element[@name='{$siblingName}']");
            if ($element) {
                $insertBefore = $before ? $element : $element->nextSibling;
            }
        }

        if ($node !== $insertBefore) {
            $parentNode->insertBefore($node, $insertBefore);
        }
        return $this;
    }
}
