<?php
/**
 * An associative data structure, that features "nested set" parent-child relations
 *
 * @category    Magento
 * @package     Magento_Data
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Data_Structure
{
    /**
     * Reserved keys for storing structural relations
     */
    const PARENT   = 'parent';
    const CHILDREN = 'children';
    const GROUPS   = 'groups';

    /**
     * @var array
     */
    protected $_elements = array();

    /**
     * Set elements in constructor
     *
     * @param array $elements
     */
    public function __construct(array $elements = null)
    {
        if (null !== $elements) {
            $this->importElements($elements);
        }
    }

    /**
     * Set elements from external source
     *
     * @param array $elements
     * @throws Magento_Exception if any format issues identified
     */
    public function importElements(array $elements)
    {
        $this->_elements = $elements;
        foreach ($elements as $id => $element) {
            if (is_numeric($id)) {
                throw new Magento_Exception("Element ID must not be numeric: '{$id}'.");
            }
            $this->_assertParentRelation($id);
            if (isset($element[self::GROUPS])) {
                $groups = $element[self::GROUPS];
                $this->_assertArray($groups);
                foreach ($groups as $groupName => $group) {
                    $this->_assertArray($group);
                    if ($group !== array_flip($group)) {
                        throw new Magento_Exception("Invalid format of group '{$groupName}': " . var_export($group, 1));
                    }
                    foreach ($group as $elementId) {
                        $this->_assertElementExists($elementId);
                    }
                }
            }
        }
    }

    /**
     * Verify relations of parent-child
     *
     * @param string $id
     * @throws Magento_Exception
     */
    protected function _assertParentRelation($id)
    {
        $element = $this->_elements[$id];

        // element presence in its parent's nested set
        if (isset($element[self::PARENT])) {
            $parentId = $element[self::PARENT];
            $this->_assertElementExists($parentId);
            if (empty($this->_elements[$parentId][self::CHILDREN][$id])) {
                throw new Magento_Exception(
                    "Broken parent-child relation: the '{$id}' is not in the nested set of '{$parentId}'."
                );
            }
        }

        // element presence in its children
        if (isset($element[self::CHILDREN])) {
            $children = $element[self::CHILDREN];
            $this->_assertArray($children);
            if ($children !== array_flip(array_flip($children))) {
                throw new Magento_Exception('Invalid format of children: ' . var_export($children, 1));
            }
            foreach ($children as $childId => $alias) {
                $this->_assertElementExists($childId);
                if (!isset($this->_elements[$childId][self::PARENT])
                    || $id !== $this->_elements[$childId][self::PARENT]
                ) {
                    throw new Magento_Exception(
                        "Broken parent-child relation: the '{$childId}' is supposed to have '{$id}' as parent."
                    );
                }
            }
        }
    }

    /**
     * Dump all elements
     *
     * @return array
     */
    public function exportElements()
    {
        return $this->_elements;
    }

    /**
     * Create new element
     *
     * @param string $id
     * @param array $data
     * @throws Magento_Exception if an element with this id already exists
     */
    public function createElement($id, array $data)
    {
        if (isset($this->_elements[$id])) {
            throw new Magento_Exception("Element with ID '{$id}' already exists.");
        }
        $this->_elements[$id] = array();
        foreach ($data as $key => $value) {
            $this->setAttribute($id, $key, $value);
        }
    }

    /**
     * Get existing element
     *
     * @param string $id
     * @return array|bool
     */
    public function getElement($id)
    {
        return isset($this->_elements[$id]) ? $this->_elements[$id] : false;
    }

    /**
     * Whether specified element exists
     *
     * @param string $id
     * @return bool
     */
    public function hasElement($id)
    {
        return isset($this->_elements[$id]);
    }

    /**
     * Remove element with specified ID from the structure
     *
     * Can recursively delete all child elements.
     * Returns false if there was no element found, therefore was nothing to delete.
     *
     * @param string $id
     * @param bool $recursive
     * @return bool
     */
    public function unsetElement($id, $recursive = true)
    {
        if (isset($this->_elements[$id][self::CHILDREN])) {
            foreach (array_keys($this->_elements[$id][self::CHILDREN]) as $childId) {
                $this->_assertElementExists($childId);
                if ($recursive) {
                    $this->unsetElement($childId, $recursive);
                } else {
                    unset($this->_elements[$childId][self::PARENT]);
                }
            }
        }
        $wasFound = isset($this->_elements[$id]);
        unset($this->_elements[$id]);
        return $wasFound;
    }

    /**
     * Set an arbitrary value to specified element attribute
     *
     * @param string $elementId
     * @param string $attribute
     * @param mixed $value
     * @throws InvalidArgumentException
     * @return Magento_Data_Structure
     */
    public function setAttribute($elementId, $attribute, $value)
    {
        $this->_assertElementExists($elementId);
        switch ($attribute) {
            case self::PARENT: // break is intentionally omitted
            case self::CHILDREN:
            case self::GROUPS:
                throw new InvalidArgumentException("Attribute '{$attribute}' is reserved and cannot be set.");
            default:
                $this->_elements[$elementId][$attribute] = $value;
        }
        return $this;
    }

    /**
     * Get element attribute
     *
     * @param string $elementId
     * @param string $attribute
     * @return bool|mixed
     */
    public function getAttribute($elementId, $attribute)
    {
        $this->_assertElementExists($elementId);
        if (isset($this->_elements[$elementId][$attribute])) {
            return $this->_elements[$elementId][$attribute];
        }
        return false;
    }

    /**
     * Rename element ID
     *
     * @param string $oldId
     * @param string $newId
     * @return Magento_Data_Structure
     * @throws Magento_Exception if trying to overwrite another element
     */
    public function renameElement($oldId, $newId)
    {
        $this->_assertElementExists($oldId);
        if (!$newId || isset($this->_elements[$newId])) {
            throw new Magento_Exception("Element with ID '{$newId}' is already defined.");
        }

        // rename in registry
        $this->_elements[$newId] = $this->_elements[$oldId];

        // rename references in children
        if (isset($this->_elements[$oldId][self::CHILDREN])) {
            foreach (array_keys($this->_elements[$oldId][self::CHILDREN]) as $childId) {
                $this->_assertElementExists($childId);
                $this->_elements[$childId][self::PARENT] = $newId;
            }
        }

        // rename key in its parent's children array
        if (isset($this->_elements[$oldId][self::PARENT]) && $parentId = $this->_elements[$oldId][self::PARENT]) {
            $alias = $this->_elements[$parentId][self::CHILDREN][$oldId];
            $offset = $this->_getChildOffset($parentId, $oldId);
            unset($this->_elements[$parentId][self::CHILDREN][$oldId]);
            $this->setAsChild($newId, $parentId, $alias, $offset);
        }

        unset($this->_elements[$oldId]);
        return $this;
    }

    /**
     * Set element as a child to another element
     *
     * @param string $elementId
     * @param string $parentId
     * @param string $alias
     * @param int|null $position
     * @see _insertChild() for position explanation
     * @throws Magento_Exception if attempting to set parent as child to its child (recursively)
     */
    public function setAsChild($elementId, $parentId, $alias = '', $position = null)
    {
        if ($elementId == $parentId) {
            throw new Magento_Exception("The '{$elementId}' cannot be set as child to itself.");
        }
        if ($this->_isParentRecursively($elementId, $parentId)) {
            throw new Magento_Exception("The '{$elementId}' is a parent of '{$parentId}' recursively, "
                . "therefore '{$elementId}' cannot be set as child to it."
            );
        }
        $this->unsetChild($elementId);
        unset($this->_elements[$parentId][self::CHILDREN][$elementId]);
        $this->_insertChild($parentId, $elementId, $position, $alias);
    }

    /**
     * Unset element as a child of another element
     *
     * Note that only parent-child relations will be deleted. Element itself will be retained.
     * The method is polymorphic:
     *   1 argument: element ID which is supposedly a child of some element
     *   2 arguments: parent element ID and child alias
     *
     * @param string $elementId ID of an element or its parent element
     * @param string|null $alias
     */
    public function unsetChild($elementId, $alias = null)
    {
        if (null === $alias) {
            $childId = $elementId;
        } else {
            $childId = $this->getChildId($elementId, $alias);
        }
        $parentId = $this->getParentId($childId);
        unset($this->_elements[$childId][self::PARENT]);
        if ($parentId) {
            unset($this->_elements[$parentId][self::CHILDREN][$childId]);
            if (empty($this->_elements[$parentId][self::CHILDREN])) {
                unset($this->_elements[$parentId][self::CHILDREN]);
            }
        }
    }

    /**
     * Reorder a child element relatively to specified position
     *
     * Returns new position of the reordered element
     *
     * @param string $parentId
     * @param string $childId
     * @param int|null $position
     * @return int
     * @see _insertChild() for position explanation
     */
    public function reorderChild($parentId, $childId, $position)
    {
        $alias = $this->getChildAlias($parentId, $childId);
        $currentOffset = $this->_getChildOffset($parentId, $childId);
        $offset = $position;
        if ($position > 0) {
            if ($position >= $currentOffset + 1) {
                $offset -= 1;
            }
        } elseif ($position < 0) {
            if ($position < (($currentOffset + 1) - count($this->_elements[$parentId][self::CHILDREN]))) {
                if ($position === -1) {
                    $offset = null;
                } else {
                    $offset += 1;
                }
            }
        }
        $this->unsetChild($childId);
        $this->_insertChild($parentId, $childId, $offset, $alias);
        return $this->_getChildOffset($parentId, $childId) + 1;
    }

    /**
     * Reorder an element relatively to its sibling
     *
     * $offset possible values:
     *    1,  2 -- set after the sibling towards end -- by 1, by 2 positions, etc
     *   -1, -2 -- set before the sibling towards start -- by 1, by 2 positions, etc...
     *
     * Both $childId and $siblingId must be children of the specified $parentId
     * Returns new position of the reordered element
     *
     * @param string $parentId
     * @param string $childId
     * @param string $siblingId
     * @param int $offset
     * @return int
     */
    public function reorderToSibling($parentId, $childId, $siblingId, $offset)
    {
        $this->_getChildOffset($parentId, $childId);
        if ($childId === $siblingId) {
            $newOffset = $this->_getRelativeOffset($parentId, $siblingId, $offset);
            return $this->reorderChild($parentId, $childId, $newOffset);
        }
        $alias = $this->getChildAlias($parentId, $childId);
        $this->unsetChild($childId);
        $newOffset = $this->_getRelativeOffset($parentId, $siblingId, $offset);
        $this->_insertChild($parentId, $childId, $newOffset, $alias);
        return $this->_getChildOffset($parentId, $childId) + 1;
    }

    /**
     * Calculate new offset for placing an element relatively specified sibling under the same parent
     *
     * @param string $parentId
     * @param string $siblingId
     * @param int $delta
     * @return int
     */
    private function _getRelativeOffset($parentId, $siblingId, $delta)
    {
        $newOffset = $this->_getChildOffset($parentId, $siblingId) + $delta;
        if ($delta < 0) {
            $newOffset += 1;
        }
        if ($newOffset < 0) {
            $newOffset = 0;
        }
        return $newOffset;
    }

    /**
     * Get child ID by parent ID and alias
     *
     * @param string $parentId
     * @param string $alias
     * @return string|bool
     */
    public function getChildId($parentId, $alias)
    {
        if (isset($this->_elements[$parentId][self::CHILDREN])) {
            return array_search($alias, $this->_elements[$parentId][self::CHILDREN]);
        }
        return false;
    }

    /**
     * Get all children
     *
     * Returns in format 'id' => 'alias'
     *
     * @param string $parentId
     * @return array
     */
    public function getChildren($parentId)
    {
        return isset($this->_elements[$parentId][self::CHILDREN])
            ? $this->_elements[$parentId][self::CHILDREN] : array();
    }

    /**
     * Get name of parent element
     *
     * @param string $childId
     * @return string|bool
     */
    public function getParentId($childId)
    {
        return isset($this->_elements[$childId][self::PARENT])
            ? $this->_elements[$childId][self::PARENT] : false;
    }

    /**
     * Get element alias by name
     *
     * @param string $parentId
     * @param string $childId
     * @return string|bool
     */
    public function getChildAlias($parentId, $childId)
    {
        if (isset($this->_elements[$parentId][self::CHILDREN][$childId])) {
            return $this->_elements[$parentId][self::CHILDREN][$childId];
        }
        return false;
    }

    /**
     * Add element to parent group
     *
     * @param string $childId
     * @param string $groupName
     * @return bool
     */
    public function addToParentGroup($childId, $groupName)
    {
        $parentId = $this->getParentId($childId);
        if ($parentId) {
            $this->_assertElementExists($parentId);
            $this->_elements[$parentId][self::GROUPS][$groupName][$childId] = $childId;
            return true;
        }
        return false;
    }

    /**
     * Get element IDs for specified group
     *
     * Note that it is expected behavior if a child has been moved out from this parent,
     * but still remained in the group of old parent. The method will return only actual children.
     * This is intentional, in case if the child returns back to the old parent.
     *
     * @param string $parentId Name of an element containing group
     * @param string $groupName
     * @return array
     */
    public function getGroupChildNames($parentId, $groupName)
    {
        $result = array();
        if (isset($this->_elements[$parentId][self::GROUPS][$groupName])) {
            foreach ($this->_elements[$parentId][self::GROUPS][$groupName] as $childId) {
                if (isset($this->_elements[$parentId][self::CHILDREN][$childId])) {
                    $result[] = $childId;
                }
            }
        }
        return $result;
    }

    /**
     * Calculate a relative offset of a child element in specified parent
     *
     * @param string $parentId
     * @param string $childId
     * @return int
     * @throws Magento_Exception if specified elements have no parent-child relation
     */
    protected function _getChildOffset($parentId, $childId)
    {
        $index = array_search($childId, array_keys($this->getChildren($parentId)));
        if (false === $index) {
            throw new Magento_Exception("The '{$childId}' is not a child of '{$parentId}'.");
        }
        return $index;
    }

    /**
     * Traverse through hierarchy and detect if the "potential parent" is a parent recursively to specified "child"
     *
     * @param string $childId
     * @param string $potentialParentId
     * @return bool
     */
    private function _isParentRecursively($childId, $potentialParentId)
    {
        $parentId = $this->getParentId($potentialParentId);
        if (!$parentId) {
            return false;
        }
        if ($parentId === $childId) {
            return true;
        }
        return $this->_isParentRecursively($childId, $parentId);
    }

    /**
     * Insert an existing element as a child to existing element
     *
     * The element must not be a child to any other element
     * The target parent element must not have it as a child already
     *
     * Offset -- into which position to insert:
     *   0     -- set as 1st
     *   1,  2 -- after 1st, second, etc...
     *  -1, -2 -- before last, before second last, etc...
     *   null  -- set as last
     *
     * @param string $targetParentId
     * @param string $elementId
     * @param int|null $offset
     * @param string $alias
     * @throws Magento_Exception
     */
    protected function _insertChild($targetParentId, $elementId, $offset, $alias)
    {
        $alias = $alias ?: $elementId;

        // validate
        $this->_assertElementExists($elementId);
        if (!empty($this->_elements[$elementId][self::PARENT])) {
            throw new Magento_Exception(
                "The element '{$elementId}' already has a parent: '{$this->_elements[$elementId][self::PARENT]}'"
            );
        }
        $this->_assertElementExists($targetParentId);
        $children = $this->getChildren($targetParentId);
        if (isset($children[$elementId])) {
            throw new Magento_Exception("The element '{$elementId}' already a child of '{$targetParentId}'");
        }
        if (false !== array_search($alias, $children)) {
            throw new Magento_Exception("The element '{$targetParentId}' already has a child with alias '{$alias}'");
        }

        // insert
        if (null === $offset) {
            $offset = count($children);
        }
        $this->_elements[$targetParentId][self::CHILDREN] = array_merge(
            array_slice($children, 0, $offset),
            array($elementId => $alias),
            array_slice($children, $offset)
        );
        $this->_elements[$elementId][self::PARENT] = $targetParentId;
    }

    /**
     * Check if specified element exists
     *
     * @param string $id
     * @throws Magento_Exception if doesn't exist
     */
    private function _assertElementExists($id)
    {
        if (!isset($this->_elements[$id])) {
            throw new Magento_Exception("No element found with ID '{$id}'.");
        }
    }

    /**
     * Check if it is an array
     *
     * @param array $value
     * @throws Magento_Exception
     */
    private function _assertArray($value)
    {
        if (!is_array($value)) {
            throw new Magento_Exception("An array expected: " . var_export($value, 1));
        }
    }
}
