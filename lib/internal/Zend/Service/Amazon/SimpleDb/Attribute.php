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
 * @package    Zend_Service_Amazon
 * @subpackage SimpleDb
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Response.php 17539 2009-08-10 22:51:26Z mikaelkael $
 */

/**
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage SimpleDb
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Amazon_SimpleDb_Attribute
{
    protected $_itemName;
    protected $_name;
    protected $_values;

    /**
     * Constructor
     * 
     * @param  string $itemName 
     * @param  string $name 
     * @param  array $values 
     * @return void
     */
    function __construct($itemName, $name, $values) 
    {
        $this->_itemName = $itemName;
        $this->_name     = $name;

        if (!is_array($values)) {
            $this->_values = array($values);
        } else {
            $this->_values = $values;
        }
    }

	/**
     * Return the item name to which the attribute belongs
     *
     * @return string
     */
    public function getItemName ()
    {
        return $this->_itemName;
    }

	/**
     * Retrieve attribute values
     *
     * @return array
     */
    public function getValues()
    {
        return $this->_values;
    }

	/**
     * Retrieve the attribute name
     *
     * @return string
     */
    public function getName ()
    {
        return $this->_name;
    }
    
    /**
     * Add value
     * 
     * @param  mixed $value 
     * @return void
     */
    public function addValue($value)
    {
        if (is_array($value)) {
             $this->_values += $value;   
        } else {
            $this->_values[] = $value;
        }
    }

    public function setValues($values)
    {
        if (!is_array($values)) {
            $values = array($values);
        }
        $this->_values = $values;
    }
}
