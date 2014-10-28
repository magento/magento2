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
namespace Magento\Framework\View\Layout;

/**
 * Layout structure model
 */
class ScheduledStructure
{
    /**
     * Information about structural elements, scheduled for creation
     *
     * @var array
     */
    protected $_scheduledStructure;

    /**
     * Full information about elements to be populated in the layout structure after generating structure
     *
     * @var array
     */
    protected $_scheduledElements;

    /**
     * Scheduled structure elements moves
     *
     * @var array
     */
    protected $_scheduledMoves;

    /**
     * Scheduled structure elements removes
     *
     * @var array
     */
    protected $_scheduledRemoves;

    /**
     * Materialized paths for overlapping workaround of scheduled structural elements
     *
     * @var array
     */
    protected $_scheduledPaths;

    /**
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(array $data = array())
    {
        $this->_scheduledStructure = isset($data['scheduledStructure']) ? $data['scheduledStructure'] : array();
        $this->_scheduledElements = isset($data['scheduledElements']) ? $data['scheduledElements'] : array();
        $this->_scheduledMoves = isset($data['scheduledMoves']) ? $data['scheduledMoves'] : array();
        $this->_scheduledRemoves = isset($data['scheduledRemoves']) ? $data['scheduledRemoves'] : array();
        $this->_scheduledPaths = isset($data['scheduledPaths']) ? $data['scheduledPaths'] : array();
    }

    /**
     * Get elements to move
     *
     * @return array
     */
    public function getListToMove()
    {
        return array_keys(array_intersect_key($this->_scheduledElements, $this->_scheduledMoves));
    }

    /**
     * Get elements to remove
     *
     * @return array
     */
    public function getListToRemove()
    {
        return array_keys(array_intersect_key($this->_scheduledElements, $this->_scheduledRemoves));
    }

    /**
     * Get scheduled elements list
     *
     * @return array
     */
    public function getElements()
    {
        return $this->_scheduledElements;
    }

    /**
     * Get element by name
     *
     * @param string $elementName
     * @param array $default
     * @return bool|array
     */
    public function getElement($elementName, $default = array())
    {
        return $this->hasElement($elementName) ? $this->_scheduledElements[$elementName] : $default;
    }

    /**
     * Check if scheduled elements list is empty
     *
     * @return bool
     */
    public function isElementsEmpty()
    {
        return empty($this->_scheduledElements);
    }

    /**
     * Add element to scheduled elements list
     *
     * @param string $elementName
     * @param array $data
     * @return void
     */
    public function setElement($elementName, array $data)
    {
        $this->_scheduledElements[$elementName] = $data;
    }

    /**
     * Check if element present in scheduled elements list
     *
     * @param string $elementName
     * @return bool
     */
    public function hasElement($elementName)
    {
        return isset($this->_scheduledElements[$elementName]);
    }

    /**
     * Unset specified element from scheduled elements list
     *
     * @param string $elementName
     * @return void
     */
    public function unsetElement($elementName)
    {
        unset($this->_scheduledElements[$elementName]);
    }

    /**
     * Get element to move by name
     *
     * @param string $elementName
     * @param mixed $default
     * @return mixed
     */
    public function getElementToMove($elementName, $default = null)
    {
        return isset($this->_scheduledMoves[$elementName]) ? $this->_scheduledMoves[$elementName] : $default;
    }

    /**
     * Add element to move list
     *
     * @param string $elementName
     * @param array $data
     * @return void
     */
    public function setElementToMove($elementName, array $data)
    {
        $this->_scheduledMoves[$elementName] = $data;
    }

    /**
     * Unset removed element by name
     *
     * @param string $elementName
     * @return void
     */
    public function unsetElementFromListToRemove($elementName)
    {
        unset($this->_scheduledRemoves[$elementName]);
    }

    /**
     * Set removed element value
     *
     * @param string $elementName
     * @return void
     */
    public function setElementToRemoveList($elementName)
    {
        $this->_scheduledRemoves[$elementName] = 1;
    }

    /**
     * Get scheduled structure
     *
     * @return array
     */
    public function getStructure()
    {
        return $this->_scheduledStructure;
    }

    /**
     * Get element of scheduled structure
     *
     * @param string $elementName
     * @param mixed|null $default
     * @return mixed
     */
    public function getStructureElement($elementName, $default = null)
    {
        return $this->hasStructureElement($elementName) ? $this->_scheduledStructure[$elementName] : $default;
    }

    /**
     * Check if scheduled structure is empty
     *
     * @return bool
     */
    public function isStructureEmpty()
    {
        return empty($this->_scheduledStructure);
    }

    /**
     * Check if element present in scheduled structure elements list
     *
     * @param string $elementName
     * @return bool
     */
    public function hasStructureElement($elementName)
    {
        return isset($this->_scheduledStructure[$elementName]);
    }

    /**
     * Add element to scheduled structure elements list
     *
     * @param  string $elementName
     * @param array $data
     * @return void
     */
    public function setStructureElement($elementName, array $data)
    {
        $this->_scheduledStructure[$elementName] = $data;
    }

    /**
     * Unset scheduled structure element by name
     *
     * @param string $elementName
     * @return void
     */
    public function unsetStructureElement($elementName)
    {
        unset($this->_scheduledStructure[$elementName]);
    }

    /**
     * Get scheduled paths
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->_scheduledPaths;
    }

    /**
     * Get path from paths list
     *
     * @param string $elementName
     * @param mixed $default
     * @return mixed
     */
    public function getPath($elementName, $default = null)
    {
        return $this->hasPath($elementName) ? $this->_scheduledPaths[$elementName] : $default;
    }

    /**
     * Check if element present in scheduled paths list
     *
     * @param string $elementName
     * @return bool
     */
    public function hasPath($elementName)
    {
        return isset($this->_scheduledPaths[$elementName]);
    }

    /**
     * Add element to scheduled paths elements list
     *
     * @param string $elementName
     * @param string $data
     * @return void
     */
    public function setPathElement($elementName, $data)
    {
        $this->_scheduledPaths[$elementName] = $data;
    }

    /**
     * Unset scheduled paths element by name
     *
     * @param string $elementName
     * @return void
     */
    public function unsetPathElement($elementName)
    {
        unset($this->_scheduledPaths[$elementName]);
    }

    /**
     * Flush scheduled paths list
     *
     * @return void
     */
    public function flushPaths()
    {
        $this->_scheduledPaths = array();
    }

    /**
     * Flush scheduled structure list
     *
     * @return void
     */
    public function flushScheduledStructure()
    {
        $this->flushPaths();
        $this->_scheduledElements = array();
        $this->_scheduledStructure = array();
    }
}
