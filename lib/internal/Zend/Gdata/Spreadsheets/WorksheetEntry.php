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
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Spreadsheets
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: WorksheetEntry.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Entry
 */
#require_once 'Zend/Gdata/Entry.php';

/**
 * @see Zend_Gdata_Spreadsheets_Extension_RowCount
 */
#require_once 'Zend/Gdata/Spreadsheets/Extension/RowCount.php';

/**
 * @see Zend_Gdata_Spreadsheets_Extension_ColCount
 */
#require_once 'Zend/Gdata/Spreadsheets/Extension/ColCount.php';

/**
 * Concrete class for working with Worksheet entries.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Spreadsheets
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Spreadsheets_WorksheetEntry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Spreadsheets_WorksheetEntry';

    protected $_rowCount = null;
    protected $_colCount = null;

    /**
     * Constructs a new Zend_Gdata_Spreadsheets_WorksheetEntry object.
     *
     * @param DOMElement $element (optional) The DOMElement on which to base this object.
     */
    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Spreadsheets::$namespaces);
        parent::__construct($element);
    }

    /**
     * Retrieves a DOMElement which corresponds to this element and all
     * child properties.  This is used to build an entry back into a DOM
     * and eventually XML text for sending to the server upon updates, or
     * for application storage/persistence.
     *
     * @param DOMDocument $doc The DOMDocument used to construct DOMElements
     * @return DOMElement The DOMElement representing this element and all
     * child properties.
     */
    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_rowCount != null) {
            $element->appendChild($this->_rowCount->getDOM($element->ownerDocument));
        }
        if ($this->_colCount != null) {
            $element->appendChild($this->_colCount->getDOM($element->ownerDocument));
        }
        return $element;
    }

    /**
     * Creates individual Entry objects of the appropriate type and
     * stores them in the $_entry array based upon DOM data.
     *
     * @param DOMNode $child The DOMNode to process
     */
    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gs') . ':' . 'rowCount';
                $rowCount = new Zend_Gdata_Spreadsheets_Extension_RowCount();
                $rowCount->transferFromDOM($child);
                $this->_rowCount = $rowCount;
                break;
            case $this->lookupNamespace('gs') . ':' . 'colCount';
                $colCount = new Zend_Gdata_Spreadsheets_Extension_ColCount();
                $colCount->transferFromDOM($child);
                $this->_colCount = $colCount;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }


    /**
     * Gets the row count for this entry.
     *
     * @return string The row count for the entry.
     */
    public function getRowCount()
    {
        return $this->_rowCount;
    }

    /**
     * Gets the column count for this entry.
     *
     * @return string The column count for the entry.
     */
    public function getColumnCount()
    {
        return $this->_colCount;
    }

    /**
     * Sets the row count for this entry.
     *
     * @param string $rowCount The new row count for the entry.
     */
    public function setRowCount($rowCount)
    {
        $this->_rowCount = $rowCount;
        return $this;
    }

    /**
     * Sets the column count for this entry.
     *
     * @param string $colCount The new column count for the entry.
     */
    public function setColumnCount($colCount)
    {
        $this->_colCount = $colCount;
        return $this;
    }

    /**
     * Returns the content of all rows as an associative array
     *
     * @return array An array of rows.  Each element of the array is an associative array of data
     */
    public function getContentsAsRows()
    {
        $service = new Zend_Gdata_Spreadsheets($this->getHttpClient());
        return $service->getSpreadsheetListFeedContents($this);
    }

    /**
     * Returns the content of all cells as an associative array, indexed
     * off the cell location  (ie 'A1', 'D4', etc).  Each element of
     * the array is an associative array with a 'value' and a 'function'.
     * Only non-empty cells are returned by default.  'range' is the
     * value of the 'range' query parameter specified at:
     * http://code.google.com/apis/spreadsheets/reference.html#cells_Parameters
     *
     * @param string $range The range of cells to retrieve
     * @param boolean $empty Whether to retrieve empty cells
     * @return array An associative array of cells
     */
    public function getContentsAsCells($range = null, $empty = false)
    {
        $service = new Zend_Gdata_Spreadsheets($this->getHttpClient());
        return $service->getSpreadsheetCellFeedContents($this, $range, $empty);
    }

}
