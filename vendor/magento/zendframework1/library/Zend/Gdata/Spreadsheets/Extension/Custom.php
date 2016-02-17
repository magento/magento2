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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
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
 * Concrete class for working with custom gsx elements.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Spreadsheets
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Spreadsheets_Extension_Custom extends Zend_Gdata_Extension
{
    // custom elements have custom names.
    protected $_rootElement = null; // The name of the column
    protected $_rootNamespace = 'gsx';

    /**
     * Constructs a new Zend_Gdata_Spreadsheets_Extension_Custom object.
     * @param string $column (optional) The column/tag name of the element.
     * @param string $value (optional) The text content of the element.
     */
    public function __construct($column = null, $value = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Spreadsheets::$namespaces);
        parent::__construct();
        $this->_text = $value;
        $this->_rootElement = $column;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        return $element;
    }

    /**
     * Transfers each child and attribute into member variables.
     * This is called when XML is received over the wire and the data
     * model needs to be built to represent this XML.
     *
     * @param DOMNode $node The DOMNode that represents this object's data
     */
    public function transferFromDOM($node)
    {
        parent::transferFromDOM($node);
        $this->_rootElement = $node->localName;
    }

    /**
     * Sets the column/tag name of the element.
     * @param string $column The new column name.
     */
    public function setColumnName($column)
    {
        $this->_rootElement = $column;
        return $this;
    }

    /**
     * Gets the column name of the element
     * @return string The column name.
     */
    public function getColumnName()
    {
        return $this->_rootElement;
    }

}
