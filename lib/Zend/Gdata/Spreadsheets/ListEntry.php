<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category     Zend
 * @package      Zend_Gdata
 * @subpackage   Spreadsheets
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ListEntry.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Entry
 */
#require_once 'Zend/Gdata/Entry.php';

/**
 * @see Zend_Gdata_Spreadsheets_Extension_Custom
 */
#require_once 'Zend/Gdata/Spreadsheets/Extension/Custom.php';

/**
 * Concrete class for working with List entries.
 *
 * @category     Zend
 * @package      Zend_Gdata
 * @subpackage   Spreadsheets
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Spreadsheets_ListEntry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Spreadsheets_ListEntry';

    /**
     * List of custom row elements (Zend_Gdata_Spreadsheets_Extension_Custom),
     * indexed by order added to this entry.
     * @var array
     */
    protected $_custom = array();

    /**
     * List of custom row elements (Zend_Gdata_Spreadsheets_Extension_Custom),
     * indexed by element name.
     * @var array
     */
    protected $_customByName = array();

    /**
     * Constructs a new Zend_Gdata_Spreadsheets_ListEntry object.
     * @param DOMElement $element An existing XML element on which to base this new object.
     */
    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Spreadsheets::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if (!empty($this->_custom)) {
            foreach ($this->_custom as $custom) {
                $element->appendChild($custom->getDOM($element->ownerDocument));
            }
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        switch ($child->namespaceURI) {
        case $this->lookupNamespace('gsx');
            $custom = new Zend_Gdata_Spreadsheets_Extension_Custom($child->localName);
            $custom->transferFromDOM($child);
            $this->addCustom($custom);
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    /**
     * Gets the row elements contained by this list entry.
     * @return array The custom row elements in this list entry
     */
    public function getCustom()
    {
        return $this->_custom;
    }

    /**
     * Gets a single row element contained by this list entry using its name.
     * @param string $name The name of a custom element to return. If null
     *          or not defined, an array containing all custom elements
     *          indexed by name will be returned.
     * @return mixed If a name is specified, the
     *          Zend_Gdata_Spreadsheets_Extension_Custom element requested,
     *          is returned or null if not found. Otherwise, an array of all
     *          Zend_Gdata_Spreadsheets_Extension_Custom elements is returned
     *          indexed by name.
     */
    public function getCustomByName($name = null)
    {
        if ($name === null) {
            return $this->_customByName;
        } else {
            if (array_key_exists($name, $this->customByName)) {
                return $this->_customByName[$name];
            } else {
                return null;
            }
        }
    }

    /**
     * Sets the row elements contained by this list entry. If any
     * custom row elements were previously stored, they will be overwritten.
     * @param array $custom The custom row elements to be contained in this
     *          list entry.
     * @return Zend_Gdata_Spreadsheets_ListEntry Provides a fluent interface.
     */
    public function setCustom($custom)
    {
        $this->_custom = array();
        foreach ($custom as $c) {
            $this->addCustom($c);
        }
        return $this;
    }

    /**
     * Add an individual custom row element to this list entry.
     * @param Zend_Gdata_Spreadsheets_Extension_Custom $custom The custom
     *             element to be added.
     * @return Zend_Gdata_Spreadsheets_ListEntry Provides a fluent interface.
     */
    public function addCustom($custom)
    {
        $this->_custom[] = $custom;
        $this->_customByName[$custom->getColumnName()] = $custom;
        return $this;
    }

    /**
     * Remove an individual row element from this list entry by index. This
     * will cause the array to be re-indexed.
     * @param int $index The index of the custom element to be deleted.
     * @return Zend_Gdata_Spreadsheets_ListEntry Provides a fluent interface.
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    public function removeCustom($index)
    {
        if (array_key_exists($index, $this->_custom)) {
            $element = $this->_custom[$index];
            // Remove element
            unset($this->_custom[$index]);
            // Re-index the array
            $this->_custom = array_values($this->_custom);
            // Be sure to delete form both arrays!
            $key = array_search($element, $this->_customByName);
            unset($this->_customByName[$key]);
        } else {
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                'Element does not exist.');
        }
        return $this;
    }

    /**
     * Remove an individual row element from this list entry by name.
     * @param string $name The name of the custom element to be deleted.
     * @return Zend_Gdata_Spreadsheets_ListEntry Provides a fluent interface.
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    public function removeCustomByName($name)
    {
        if (array_key_exists($name, $this->_customByName)) {
            $element = $this->_customByName[$name];
            // Remove element
            unset($this->_customByName[$name]);
            // Be sure to delete from both arrays!
            $key = array_search($element, $this->_custom);
            unset($this->_custom[$key]);
        } else {
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                'Element does not exist.');
        }
        return $this;
    }

}
