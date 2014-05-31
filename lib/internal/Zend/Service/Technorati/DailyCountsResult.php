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
 * @package    Zend_Service
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: DailyCountsResult.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Service_Technorati_Result
 */
#require_once 'Zend/Service/Technorati/Result.php';


/**
 * Represents a single Technorati DailyCounts query result object.
 * It is never returned as a standalone object,
 * but it always belongs to a valid Zend_Service_Technorati_DailyCountsResultSet object.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Technorati_DailyCountsResult extends Zend_Service_Technorati_Result
{
    /**
     * Date of count.
     *
     * @var     Zend_Date
     * @access  protected
     */
    protected $_date;

    /**
     * Number of posts containing query on given date.
     *
     * @var     int
     * @access  protected
     */
    protected $_count;


    /**
     * Constructs a new object object from DOM Document.
     *
     * @param   DomElement $dom the ReST fragment for this object
     */
    public function __construct(DomElement $dom)
    {
        $this->_fields = array( '_date'   => 'date',
                                '_count'  => 'count');
        parent::__construct($dom);

        // filter fields
        $this->_date  = new Zend_Date(strtotime($this->_date));
        $this->_count = (int) $this->_count;
    }

    /**
     * Returns the date of count.
     *
     * @return  Zend_Date
     */
    public function getDate() {
        return $this->_date;
    }

    /**
     * Returns the number of posts containing query on given date.
     *
     * @return  int
     */
    public function getCount() {
        return $this->_count;
    }
}
