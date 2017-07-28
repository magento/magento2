<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

/**
 * Layout structure model
 *
 * @api
 * @since 2.0.0
 */
class ScheduledStructure
{
    /**#@+
     * Keys for array of elements to sort
     */
    const ELEMENT_NAME = 'elementName';
    const ELEMENT_PARENT_NAME = 'parentName';
    const ELEMENT_OFFSET_OR_SIBLING  = 'offsetOrSibling';
    const ELEMENT_IS_AFTER = 'isAfter';
    /**#@-*/

    /**
     * Map of class properties.
     *
     * @var array
     * @since 2.2.0
     */
    private $serializableProperties = [
        'scheduledStructure',
        'scheduledData',
        'scheduledElements',
        'scheduledMoves',
        'scheduledRemoves',
        'scheduledPaths',
        'elementsToSort',
        'brokenParent',
    ];

    /**
     * Information about structural elements, scheduled for creation
     *
     * @var array
     * @since 2.0.0
     */
    protected $scheduledStructure = [];

    /**
     * Scheduled structure data
     *
     * @var array
     * @since 2.0.0
     */
    protected $scheduledData = [];

    /**
     * Full information about elements to be populated in the layout structure after generating structure
     *
     * @var array
     * @since 2.0.0
     */
    protected $scheduledElements = [];

    /**
     * Scheduled structure elements moves
     *
     * @var array
     * @since 2.0.0
     */
    protected $scheduledMoves = [];

    /**
     * Scheduled structure elements removes
     *
     * @var array
     * @since 2.0.0
     */
    protected $scheduledRemoves = [];

    /**
     * Materialized paths for overlapping workaround of scheduled structural elements
     *
     * @var array
     * @since 2.0.0
     */
    protected $scheduledPaths = [];

    /**
     * Elements with reference to non-existing parent element
     *
     * @var array
     * @since 2.0.0
     */
    protected $brokenParent = [];

    /**
     * Elements that need to sort
     *
     * @var array
     * @since 2.0.0
     */
    protected $elementsToSort = [];

    /**
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(array $data = [])
    {
        $this->populateWithArray($data);
    }

    /**
     * Set elements to sort
     *
     * @param string $parentName
     * @param string $elementName
     * @param string|int|null $offsetOrSibling
     * @param bool $isAfter
     * @return void
     * @since 2.0.0
     */
    public function setElementToSortList($parentName, $elementName, $offsetOrSibling, $isAfter = true)
    {
        $this->elementsToSort[$elementName] = [
            self::ELEMENT_NAME => $elementName,
            self::ELEMENT_PARENT_NAME => $parentName,
            self::ELEMENT_OFFSET_OR_SIBLING => $offsetOrSibling,
            self::ELEMENT_IS_AFTER => $isAfter
        ];
    }

    /**
     * Check if elements list of sorting is empty
     *
     * @return bool
     * @since 2.0.0
     */
    public function isListToSortEmpty()
    {
        return empty($this->elementsToSort);
    }

    /**
     * Unset specified element from list of sorting
     *
     * @param string $elementName
     * @return void
     * @since 2.0.0
     */
    public function unsetElementToSort($elementName)
    {
        unset($this->elementsToSort[$elementName]);
    }

    /**
     * Get element to sort by name
     *
     * @param string $elementName
     * @param array $default
     * @return array
     * @since 2.0.0
     */
    public function getElementToSort($elementName, array $default = [])
    {
        return isset($this->elementsToSort[$elementName]) ? $this->elementsToSort[$elementName] : $default;
    }

    /**
     * Get elements to sort
     *
     * @return array
     * @since 2.0.0
     */
    public function getListToSort()
    {
        return $this->elementsToSort;
    }

    /**
     * Get elements to move
     *
     * @return array
     * @since 2.0.0
     */
    public function getListToMove()
    {
        return array_keys(array_intersect_key($this->scheduledElements, $this->scheduledMoves));
    }

    /**
     * Get elements to remove
     *
     * @return array
     * @since 2.0.0
     */
    public function getListToRemove()
    {
        return array_keys(array_intersect_key(
            $this->scheduledElements,
            array_merge($this->scheduledRemoves, $this->brokenParent)
        ));
    }

    /**
     * Get scheduled elements list
     *
     * @return array
     * @since 2.0.0
     */
    public function getElements()
    {
        return $this->scheduledElements;
    }

    /**
     * Get element by name
     *
     * @param string $elementName
     * @param array $default
     * @return bool|array
     * @since 2.0.0
     */
    public function getElement($elementName, $default = [])
    {
        return $this->hasElement($elementName) ? $this->scheduledElements[$elementName] : $default;
    }

    /**
     * Check if scheduled elements list is empty
     *
     * @return bool
     * @since 2.0.0
     */
    public function isElementsEmpty()
    {
        return empty($this->scheduledElements);
    }

    /**
     * Add element to scheduled elements list
     *
     * @param string $elementName
     * @param array $data
     * @return void
     * @since 2.0.0
     */
    public function setElement($elementName, array $data)
    {
        $this->scheduledElements[$elementName] = $data;
    }

    /**
     * Check if element present in scheduled elements list
     *
     * @param string $elementName
     * @return bool
     * @since 2.0.0
     */
    public function hasElement($elementName)
    {
        return isset($this->scheduledElements[$elementName]);
    }

    /**
     * Unset specified element from scheduled elements list
     *
     * @param string $elementName
     * @return void
     * @since 2.0.0
     */
    public function unsetElement($elementName)
    {
        unset($this->scheduledElements[$elementName]);
    }

    /**
     * Get element to move by name
     *
     * @param string $elementName
     * @param mixed $default
     * @return mixed
     * @since 2.0.0
     */
    public function getElementToMove($elementName, $default = null)
    {
        return isset($this->scheduledMoves[$elementName]) ? $this->scheduledMoves[$elementName] : $default;
    }

    /**
     * Add element to move list
     *
     * @param string $elementName
     * @param array $data
     * @return void
     * @since 2.0.0
     */
    public function setElementToMove($elementName, array $data)
    {
        $this->scheduledMoves[$elementName] = $data;
    }

    /**
     * Unset removed element by name
     *
     * @param string $elementName
     * @return void
     * @since 2.0.0
     */
    public function unsetElementFromListToRemove($elementName)
    {
        unset($this->scheduledRemoves[$elementName]);
    }

    /**
     * Set removed element value
     *
     * @param string $elementName
     * @return void
     * @since 2.0.0
     */
    public function setElementToRemoveList($elementName)
    {
        $this->scheduledRemoves[$elementName] = 1;
    }

    /**
     * Get scheduled structure
     *
     * @return array
     * @since 2.0.0
     */
    public function getStructure()
    {
        return $this->scheduledStructure;
    }

    /**
     * Get element of scheduled structure
     *
     * @param string $elementName
     * @param mixed|null $default
     * @return mixed
     * @since 2.0.0
     */
    public function getStructureElement($elementName, $default = null)
    {
        return $this->hasStructureElement($elementName) ? $this->scheduledStructure[$elementName] : $default;
    }

    /**
     * Check if scheduled structure is empty
     *
     * @return bool
     * @since 2.0.0
     */
    public function isStructureEmpty()
    {
        return empty($this->scheduledStructure);
    }

    /**
     * Check if element present in scheduled structure elements list
     *
     * @param string $elementName
     * @return bool
     * @since 2.0.0
     */
    public function hasStructureElement($elementName)
    {
        return isset($this->scheduledStructure[$elementName]);
    }

    /**
     * Add element to scheduled structure elements list
     *
     * @param string $elementName
     * @param array $data
     * @return void
     * @since 2.0.0
     */
    public function setStructureElement($elementName, array $data)
    {
        $this->scheduledStructure[$elementName] = $data;
    }

    /**
     * Unset scheduled structure element by name
     *
     * @param string $elementName
     * @return void
     * @since 2.0.0
     */
    public function unsetStructureElement($elementName)
    {
        unset($this->scheduledStructure[$elementName]);
        unset($this->scheduledData[$elementName]);
    }

    /**
     * Get scheduled data for element
     *
     * @param string $elementName
     * @param null $default
     * @return null
     * @since 2.0.0
     */
    public function getStructureElementData($elementName, $default = null)
    {
        return isset($this->scheduledData[$elementName]) ? $this->scheduledData[$elementName] : $default;
    }

    /**
     * Set scheduled data for element
     *
     * @param string $elementName
     * @param array $data
     * @return void
     * @since 2.0.0
     */
    public function setStructureElementData($elementName, array $data)
    {
        $this->scheduledData[$elementName] = $data;
    }

    /**
     * Get scheduled paths
     *
     * @return array
     * @since 2.0.0
     */
    public function getPaths()
    {
        return $this->scheduledPaths;
    }

    /**
     * Get path from paths list
     *
     * @param string $elementName
     * @param mixed $default
     * @return mixed
     * @since 2.0.0
     */
    public function getPath($elementName, $default = null)
    {
        return $this->hasPath($elementName) ? $this->scheduledPaths[$elementName] : $default;
    }

    /**
     * Check if element present in scheduled paths list
     *
     * @param string $elementName
     * @return bool
     * @since 2.0.0
     */
    public function hasPath($elementName)
    {
        return isset($this->scheduledPaths[$elementName]);
    }

    /**
     * Add element to scheduled paths elements list
     *
     * @param string $elementName
     * @param string $data
     * @return void
     * @since 2.0.0
     */
    public function setPathElement($elementName, $data)
    {
        $this->scheduledPaths[$elementName] = $data;
    }

    /**
     * Unset scheduled paths element by name
     *
     * @param string $elementName
     * @return void
     * @since 2.0.0
     */
    public function unsetPathElement($elementName)
    {
        unset($this->scheduledPaths[$elementName]);
    }

    /**
     * Remove element from broken parent list
     *
     * @param string $elementName
     * @return void
     * @since 2.0.0
     */
    public function unsetElementFromBrokenParentList($elementName)
    {
        unset($this->brokenParent[$elementName]);
    }

    /**
     * Set element to broken parent list
     *
     * @param string $elementName
     * @return void
     * @since 2.0.0
     */
    public function setElementToBrokenParentList($elementName)
    {
        $this->brokenParent[$elementName] = 1;
    }

    /**
     * Flush scheduled paths list
     *
     * @return void
     * @since 2.0.0
     */
    public function flushPaths()
    {
        $this->scheduledPaths = [];
    }

    /**
     * Flush scheduled structure list
     *
     * @return void
     * @since 2.0.0
     */
    public function flushScheduledStructure()
    {
        $this->flushPaths();
        $this->scheduledElements = [];
        $this->scheduledStructure = [];
    }

    /**
     * Reformat 'Layout scheduled structure' to array.
     *
     * @return array
     * @since 2.2.0
     */
    public function __toArray()
    {
        $result = [];
        foreach ($this->serializableProperties as $property) {
            $result[$property] = $this->{$property};
        }

        return $result;
    }

    /**
     * Update 'Layout scheduled structure' data.
     *
     * @param array $data
     * @return void
     * @since 2.2.0
     */
    public function populateWithArray(array $data)
    {
        foreach ($this->serializableProperties as $property) {
            $this->{$property} = $this->getArrayValueByKey($property, $data);
        }
    }

    /**
     * Get value from array by key.
     *
     * @param string $key
     * @param array $array
     * @return array
     * @since 2.2.0
     */
    private function getArrayValueByKey($key, array $array)
    {
        return isset($array[$key]) ? $array[$key] : [];
    }
}
