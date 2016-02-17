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
 * @package    Zend_Wildfire
 * @subpackage Plugin
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Wildfire_Plugin_FirePhp */
#require_once 'Zend/Wildfire/Plugin/FirePhp.php';

/** Zend_Wildfire_Plugin_FirePhp_Message */
#require_once 'Zend/Wildfire/Plugin/FirePhp/Message.php';

/**
 * A message envelope that can be updated for the duration of the requet before
 * it gets flushed at the end of the request.
 *
 * @category   Zend
 * @package    Zend_Wildfire
 * @subpackage Plugin
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Wildfire_Plugin_FirePhp_TableMessage extends Zend_Wildfire_Plugin_FirePhp_Message
{
    /**
     * The header of the table containing all columns
     * @var array
     */
    protected $_header = null;

    /**
     * The rows of the table
     * $var array
     */
    protected $_rows = array();

    /**
     * Constructor
     *
     * @param string $label The label of the table
     */
    function __construct($label)
    {
        parent::__construct(Zend_Wildfire_Plugin_FirePhp::TABLE, null);
        $this->setLabel($label);
    }

    /**
     * Set the table header
     *
     * @param array $header The header columns
     * @return void
     */
    public function setHeader($header)
    {
        $this->_header = $header;
    }

    /**
     * Append a row to the end of the table.
     *
     * @param array $row An array of column values representing a row.
     * @return void
     */
    public function addRow($row)
    {
        $this->_rows[] = $row;
    }

    /**
     * Get the actual message to be sent in its final format.
     *
     * @return mixed Returns the message to be sent.
     */
    public function getMessage()
    {
        $table = $this->_rows;
        if($this->_header) {
            array_unshift($table,$this->_header);
        }
        return $table;
    }

    /**
     * Returns the row at the given index
     *
     * @param integer $index The index of the row
     * @return array Returns the row
     * @throws Zend_Wildfire_Exception
     */
    public function getRowAt($index)
    {
        $count = $this->getRowCount();

        if($index < 0 || $index > $count-1) {
            #require_once 'Zend/Wildfire/Exception.php';
            throw new Zend_Wildfire_Exception('Row index('.$index.') out of bounds('.$count.')!');
        }

        return $this->_rows[$index];
    }

    /**
     * Sets the row on the given index to a new row
     *
     * @param integer $index The index of the row
     * @param array $row The new data for the row
     * @throws Zend_Wildfire_Exception
     */
    public function setRowAt($index, $row)
    {
        $count = $this->getRowCount();

        if($index < 0 || $index > $count-1) {
            #require_once 'Zend/Wildfire/Exception.php';
            throw new Zend_Wildfire_Exception('Row index('.$index.') out of bounds('.$count.')!');
        }

        $this->_rows[$index] = $row;
    }

    /**
     * Returns the number of rows
     *
     * @return integer
     */
    public function getRowCount()
    {
        return count($this->_rows);
    }

    /**
     * Returns the last row of the table
     *
     * @return array Returns the last row
     * @throws Zend_Wildfire_Exception
     */
    public function getLastRow()
    {
        $count = $this->getRowCount();

        if($count==0) {
            #require_once 'Zend/Wildfire/Exception.php';
            throw new Zend_Wildfire_Exception('Cannot get last row as no rows exist!');
        }

        return $this->_rows[$count-1];
    }
}
