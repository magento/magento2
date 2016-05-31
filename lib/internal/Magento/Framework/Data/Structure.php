<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

use Magento\Framework\Exception\LocalizedException;

/**
 * An associative data structure, that features "nested set" parent-child relations
 */
class Structure
{
    /**
     * Reserved keys for storing structural relations
     */
    const PARENT = 'parent';

    const CHILDREN = 'children';

    const GROUPS = 'groups';

    /**
     * @var array
     */
    protected $_elements = [];

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
     * @return void
     * @throws LocalizedException if any format issues identified
     */
    public function importElements(array $elements)
    {
        $this->_elements = $elements;
        foreach ($elements as $elementId => $element) {
            if (is_numeric($elementId)) {
                throw new LocalizedException(
                    new \Magento\Framework\Phrase("Element ID must not be numeric: '%1'.", [$elementId])
                );
            }
            $this->_assertParentRelation($elementId);
            if (isset($element[self::GROUPS])) {
                $groups = $element[self::GROUPS];
                $this->_assertArray($groups);
                foreach ($groups as $groupName => $group) {
                    $this->_assertArray($group);
                    if ($group !== array_flip($group)) {
                        throw new LocalizedException(
                            new \Magento\Framework\Phrase(
                                "Invalid format of group '%1': %2",
                                [$groupName, var_export($group, 1)]
                            )
                        );
                    }
                    foreach ($group as $groupElementId) {
                        $this->_assertElementExists($groupElementId);
                    }
                }
            }
        }
    }

    /**
     * Verify relations of parent-child
     *
     * @param string $elementId
     * @return void
     * @throws LocalizedException
     */
    protected function _assertParentRelation($elementId)
    {
        $element = $this->_elements[$elementId];

        // element presence in its parent's nested set
        if (isset($element[self::PARENT])) {
            $parentId = $element[self::PARENT];
            $this->_assertElementExists($parentId);
            if (empty($this->_elements[$parentId][self::CHILDREN][$elementId])) {
                throw new LocalizedException(
                    new \Magento\Framework\Phrase(
                        "Broken parent-child relation: the '%1' is not in the nested set of '%2'.",
                        [$elementId, $parentId]
                    )
                );
            }
        }

        // element presence in its children
        if (isset($element[self::CHILDREN])) {
            $children = $element[self::CHILDREN];
            $this->_assertArray($children);
            if ($children !== array_flip(array_flip($children))) {
                throw new LocalizedException(
                    new \Magento\Framework\Phrase('Invalid format of children: %1', [var_export($children, 1)])
                );
            }
            foreach (array_keys($children) as $childId) {
                $this->_assertElementExists($childId);
                if (!isset(
                    $this->_elements[$childId][self::PARENT]
                ) || $elementId !== $this->_elements[$childId][self::PARENT]
                ) {
                    throw new LocalizedException(
                        new \Magento\Framework\Phrase(
                            "Broken parent-child relation: the '%1' is supposed to have '%2' as parent.",
                            [$childId, $elementId]
                        )
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
     * @param string $elementId
     * @param array $data
     * @return void
     * @throws LocalizedException if an element with this id already exists
     */
    public function createElement($elementId, array $data)
    {
        if (isset($this->_elements[$elementId])) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase("Element with ID '%1' already exists.", [$elementId])
            );
        }
        $this->_elements[$elementId] = [];
        foreach ($data as $key => $value) {
            $this->setAttribute($elementId, $key, $value);
        }
    }

    /**
     * Get existing element
     *
     * @param string $elementId
     * @return array|bool
     */
    public function getElement($elementId)
    {
        return isset($this->_elements[$elementId]) ? $this->_elements[$elementId] : false;
    }

    /**
     * Whether specified element exists
     *
     * @param string $elementId
     * @return bool
     */
    public function hasElement($elementId)
    {
        return isset($this->_elements[$elementId]);
    }

    /**
     * Remove element with specified ID from the structure
     *
     * Can recursively delete all child elements.
     * Returns false if there was no element found, therefore was nothing to delete.
     *
     * @param string $elementId
     * @param bool $recursive
     * @return bool
     */
    public function unsetElement($elementId, $recursive = true)
    {
        if (isset($this->_elements[$elementId][self::CHILDREN])) {
            foreach (array_keys($this->_elements[$elementId][self::CHILDREN]) as $childId) {
                $this->_assertElementExists($childId);
                if ($recursive) {
                    $this->unsetElement($childId, $recursive);
                } else {
                    unset($this->_elements[$childId][self::PARENT]);
                }
            }
        }
        $this->unsetChild($elementId);
        $wasFound = isset($this->_elements[$elementId]);
        unset($this->_elements[$elementId]);
        return $wasFound;
    }

    /**
     * Set an arbitrary value to specified element attribute
     *
     * @param string $elementId
     * @param string $attribute
     * @param mixed $value
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setAttribute($elementId, $attribute, $value)
    {
        $this->_assertElementExists($elementId);
        switch ($attribute) {
            case self::PARENT:
                // break is intentionally omitted
            case self::CHILDREN:
            case self::GROUPS:
                throw new \InvalidArgumentException("Attribute '{$attribute}' is reserved and cannot be set.");
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
     * @return mixed
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
     * @return $this
     * @throws LocalizedException if trying to overwrite another element
     */
    public function renameElement($oldId, $newId)
    {
        $this->_assertElementExists($oldId);
        if (!$newId || isset($this->_elements[$newId])) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase("Element with ID '%1' is already defined.", [$newId])
            );
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
        if (isset($this->_elements[$oldId][self::PARENT]) && ($parentId = $this->_elements[$oldId][self::PARENT])) {
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
     * @return void
     * @throws LocalizedException if attempting to set parent as child to its child (recursively)
     */
    public function setAsChild($elementId, $parentId, $alias = '', $position = null)
    {
        if ($elementId == $parentId) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase("The '%1' cannot be set as child to itself.", [$elementId])
            );
        }
        if ($this->_isParentRecursively($elementId, $parentId)) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase(
                    "The '%1' is a parent of '%2' recursively, therefore '%3' cannot be set as child to it.",
                    [$elementId, $parentId, $elementId]
                )
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
     * @return $this
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
        return $this;
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
            if ($position < $currentOffset + 1 - count($this->_elements[$parentId][self::CHILDREN])) {
                if ($position === -1) {
                    $offset = null;
                } else {
                    $offset += 1;
                }
            }
        }
        $this->unsetChild($childId)->_insertChild($parentId, $childId, $offset, $alias);
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
        $newOffset = $this->unsetChild($childId)->_getRelativeOffset($parentId, $siblingId, $offset);
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
        return isset(
            $this->_elements[$parentId][self::CHILDREN]
        ) ? $this->_elements[$parentId][self::CHILDREN] : [];
    }

    /**
     * Get name of parent element
     *
     * @param string $childId
     * @return string|bool
     */
    public function getParentId($childId)
    {
        return isset($this->_elements[$childId][self::PARENT]) ? $this->_elements[$childId][self::PARENT] : false;
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
        $result = [];
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
     * @throws LocalizedException if specified elements have no parent-child relation
     */
    protected function _getChildOffset($parentId, $childId)
    {
        $index = array_search($childId, array_keys($this->getChildren($parentId)));
        if (false === $index) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase("The '%1' is not a child of '%2'.", [$childId, $parentId])
            );
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
     * @return void
     * @throws LocalizedException
     */
    protected function _insertChild($targetParentId, $elementId, $offset, $alias)
    {
        $alias = $alias ?: $elementId;

        // validate
        $this->_assertElementExists($elementId);
        if (!empty($this->_elements[$elementId][self::PARENT])) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase(
                    "The element '%1' already has a parent: '%2'",
                    [$elementId, $this->_elements[$elementId][self::PARENT]]
                )
            );
        }
        $this->_assertElementExists($targetParentId);
        $children = $this->getChildren($targetParentId);
        if (isset($children[$elementId])) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase("The element '%1' already a child of '%2'", [$elementId, $targetParentId])
            );
        }
        if (false !== array_search($alias, $children)) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase(
                    "The element '%1' already has a child with alias '%2'",
                    [$targetParentId, $alias]
                )
            );
        }

        // insert
        if (null === $offset) {
            $offset = count($children);
        }
        $this->_elements[$targetParentId][self::CHILDREN] = array_merge(
            array_slice($children, 0, $offset),
            [$elementId => $alias],
            array_slice($children, $offset)
        );
        $this->_elements[$elementId][self::PARENT] = $targetParentId;
    }

    /**
     * Check if specified element exists
     *
     * @param string $elementId
     * @return void
     * @throws LocalizedException if doesn't exist
     */
    private function _assertElementExists($elementId)
    {
        if (!isset($this->_elements[$elementId])) {
            throw new \OutOfBoundsException("No element found with ID '{$elementId}'.");
        }
    }

    /**
     * Check if it is an array
     *
     * @param array $value
     * @return void
     * @throws LocalizedException
     */
    private function _assertArray($value)
    {
        if (!is_array($value)) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase("An array expected: %1", [var_export($value, 1)])
            );
        }
    }
}
