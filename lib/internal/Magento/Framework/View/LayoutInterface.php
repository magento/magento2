<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

/**
 * Interface LayoutInterface
 */
interface LayoutInterface
{
    /**
     * Retrieve the layout processor
     *
     * @return Layout\ProcessorInterface
     */
    public function getUpdate();

    /**
     * Layout xml generation
     *
     * @return LayoutInterface
     */
    public function generateXml();

    /**
     * Create structure of elements from the loaded XML configuration
     *
     * @return void
     */
    public function generateElements();

    /**
     * Find an element in layout, render it and return string with its output
     *
     * @param string $name
     * @param bool $useCache
     * @return string
     */
    public function renderElement($name, $useCache = true);

    /**
     * Add an element to output
     *
     * @param string $name
     * @return LayoutInterface
     */
    public function addOutputElement($name);

    /**
     * Get all blocks marked for output
     *
     * @return string
     */
    public function getOutput();

    /**
     * Check if element exists in layout structure
     *
     * @param string $name
     * @return bool
     */
    public function hasElement($name);

    /**
     * Remove block from registry
     *
     * @param string $name
     * @return LayoutInterface
     */
    public function unsetElement($name);

    /**
     * Retrieve all blocks from registry as array
     *
     * @return array
     */
    public function getAllBlocks();

    /**
     * Get block object by name
     *
     * @param string $name
     * @return Element\BlockInterface|bool
     */
    public function getBlock($name);

    /**
     * Get child block if exists
     *
     * @param string $parentName
     * @param string $alias
     * @return null
     */
    public function getChildBlock($parentName, $alias);

    /**
     * Set child element into layout structure
     *
     * @param string $parentName
     * @param string $elementName
     * @param string $alias
     * @return LayoutInterface
     */
    public function setChild($parentName, $elementName, $alias);

    /**
     * Reorder a child of a specified element
     *
     * If $offsetOrSibling is null, it will put the element to the end
     * If $offsetOrSibling is numeric (integer) value, it will put the element after/before specified position
     * Otherwise -- after/before specified sibling
     *
     * @param string $parentName
     * @param string $childName
     * @param string|int|null $offsetOrSibling
     * @param bool $after
     * @return void
     */
    public function reorderChild($parentName, $childName, $offsetOrSibling, $after = true);

    /**
     * Remove child element from parent
     *
     * @param string $parentName
     * @param string $alias
     * @return LayoutInterface
     */
    public function unsetChild($parentName, $alias);

    /**
     * Get list of child names
     *
     * @param string $parentName
     * @return array
     */
    public function getChildNames($parentName);

    /**
     * Get list of child blocks
     *
     * Returns associative array of <alias> => <block instance>
     *
     * @param string $parentName
     * @return array
     */
    public function getChildBlocks($parentName);

    /**
     * Get child name by alias
     *
     * @param string $parentName
     * @param string $alias
     * @return bool|string
     */
    public function getChildName($parentName, $alias);

    /**
     * Add element to parent group
     *
     * @param string $blockName
     * @param string $parentGroupName
     * @return bool
     */
    public function addToParentGroup($blockName, $parentGroupName);

    /**
     * Get element names for specified group
     *
     * @param string $blockName
     * @param string $groupName
     * @return array
     */
    public function getGroupChildNames($blockName, $groupName);

    /**
     * Gets parent name of an element with specified name
     *
     * @param string $childName
     * @return bool|string
     */
    public function getParentName($childName);

    /**
     * Block Factory
     *
     * @param  string $type
     * @param  string $name
     * @param  array $arguments
     * @return Element\BlockInterface
     */
    public function createBlock($type, $name = '', array $arguments = []);

    /**
     * Add a block to registry, create new object if needed
     *
     * @param string|\Magento\Framework\View\Element\AbstractBlock $block
     * @param string $name
     * @param string $parent
     * @param string $alias
     * @return Element\BlockInterface
     */
    public function addBlock($block, $name = '', $parent = '', $alias = '');

    /**
     * Insert container into layout structure
     *
     * @param string $name
     * @param string $label
     * @param array $options
     * @param string $parent
     * @param string $alias
     * @return void
     */
    public function addContainer($name, $label, array $options = [], $parent = '', $alias = '');

    /**
     * Rename element in layout and layout structure
     *
     * @param string $oldName
     * @param string $newName
     * @return bool
     */
    public function renameElement($oldName, $newName);

    /**
     * Get element alias by name
     *
     * @param string $name
     * @return bool|string
     */
    public function getElementAlias($name);

    /**
     * Remove an element from output
     *
     * @param string $name
     * @return LayoutInterface
     */
    public function removeOutputElement($name);

    /**
     * Retrieve messages block
     *
     * @return \Magento\Framework\View\Element\Messages
     */
    public function getMessagesBlock();

    /**
     * Get block singleton
     *
     * @param string $type
     * @return Element\BlockInterface
     */
    public function getBlockSingleton($type);

    /**
     * Get property value of an element
     *
     * @param string $name
     * @param string $attribute
     * @return mixed
     */
    public function getElementProperty($name, $attribute);

    /**
     * Whether specified element is a block
     *
     * @param string $name
     * @return bool
     */
    public function isBlock($name);

    /**
     * Checks if element with specified name is container
     *
     * @param string $name
     * @return bool
     */
    public function isContainer($name);

    /**
     * Whether the specified element may be manipulated externally
     *
     * @param string $name
     * @return bool
     */
    public function isManipulationAllowed($name);

    /**
     * Save block in blocks registry
     *
     * @param string $name
     * @param  Element\BlockInterface $block
     * @return LayoutInterface
     */
    public function setBlock($name, $block);

    /**
     * Check is exists non-cacheable layout elements
     *
     * @return bool
     */
    public function isCacheable();
}
