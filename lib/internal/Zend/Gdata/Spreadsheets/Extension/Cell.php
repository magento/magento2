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
 * @version    $Id: Cell.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Entry
 */
#require_once 'Zend/Gdata/Entry.php';

/**
 * @see Zend_Gdata_Extension
 */
#require_once 'Zend/Gdata/Extension.php';


/**
 * Concrete class for working with cell elements.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Spreadsheets
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Spreadsheets_Extension_Cell extends Zend_Gdata_Extension
{
    protected $_rootElement = 'cell';
    protected $_rootNamespace = 'gs';

    /**
     * The row attribute of this cell
     *
     * @var string
     */
    protected $_row = null;

    /**
     * The column attribute of this cell
     *
     * @var string
     */
    protected $_col = null;

    /**
     * The inputValue attribute of this cell
     *
     * @var string
     */
    protected $_inputValue = null;

    /**
     * The numericValue attribute of this cell
     *
     * @var string
     */
    protected $_numericValue = null;

    /**
     * Constructs a new Zend_Gdata_Spreadsheets_Extension_Cell element.
     *
     * @param string $text (optional) Text contents of the element.
     * @param string $row (optional) Row attribute of the element.
     * @param string $col (optional) Column attribute of the element.
     * @param string $inputValue (optional) Input value attribute of the element.
     * @param string $numericValue (optional) Numeric value attribute of the element.
     */
    public function __construct($text = null, $row = null, $col = null, $inputValue = null, $numericValue = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Spreadsheets::$namespaces);
        parent::__construct();
        $this->_text = $text;
        $this->_row = $row;
        $this->_col = $col;
        $this->_inputValue = $inputValue;
        $this->_numericValue = $numericValue;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        $element->setAttribute('row', $this->_row);
        $element->setAttribute('col', $this->_col);
        if ($this->_inputValue) $element->setAttribute('inputValue', $this->_inputValue);
        if ($this->_numericValue) $element->setAttribute('numericValue', $this->_numericValue);
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'row':
            $this->_row = $attribute->nodeValue;
            break;
        case 'col':
            $this->_col = $attribute->nodeValue;
            break;
        case 'inputValue':
            $this->_inputValue = $attribute->nodeValue;
            break;
        case 'numericValue':
            $this->_numericValue = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    /**
     * Gets the row attribute of the Cell element.
     * @return string Row of the Cell.
     */
    public function getRow()
    {
        return $this->_row;
    }

    /**
     * Gets the column attribute of the Cell element.
     * @return string Column of the Cell.
     */
    public function getColumn()
    {
        return $this->_col;
    }

    /**
     * Gets the input value attribute of the Cell element.
     * @return string Input value of the Cell.
     */
    public function getInputValue()
    {
        return $this->_inputValue;
    }

    /**
     * Gets the numeric value attribute of the Cell element.
     * @return string Numeric value of the Cell.
     */
    public function getNumericValue()
    {
        return $this->_numericValue;
    }

    /**
     * Sets the row attribute of the Cell element.
     * @param string $row New row of the Cell.
     */
    public function setRow($row)
    {
        $this->_row = $row;
        return $this;
    }

    /**
     * Sets the column attribute of the Cell element.
     * @param string $col New column of the Cell.
     */
    public function setColumn($col)
    {
        $this->_col = $col;
        return $this;
    }

    /**
     * Sets the input value attribute of the Cell element.
     * @param string $inputValue New input value of the Cell.
     */
    public function setInputValue($inputValue)
    {
        $this->_inputValue = $inputValue;
        return $this;
    }

    /**
     * Sets the numeric value attribute of the Cell element.
     * @param string $numericValue New numeric value of the Cell.
     */
    public function setNumericValue($numericValue)
    {
        $this->_numericValue = $numericValue;
    }

}
