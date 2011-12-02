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
 * @subpackage Yahoo
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: LocalResultSet.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Service_Yahoo_ResultSet
 */
#require_once 'Zend/Service/Yahoo/ResultSet.php';


/**
 * @see Zend_Service_Yahoo_LocalResult
 */
#require_once 'Zend/Service/Yahoo/LocalResult.php';


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Yahoo
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Yahoo_LocalResultSet extends Zend_Service_Yahoo_ResultSet
{
    /**
     * The URL of a webpage containing a map graphic with all returned results plotted on it.
     *
     * @var string
     */
    public $resultSetMapURL;

    /**
     * Local result set namespace
     *
     * @var string
     */
    protected $_namespace = 'urn:yahoo:lcl';


    /**
     * Initializes the local result set
     *
     * @param  DOMDocument $dom
     * @return void
     */
    public function __construct(DOMDocument $dom)
    {
        parent::__construct($dom);

        $this->resultSetMapURL = $this->_xpath->query('//yh:ResultSetMapUrl/text()')->item(0)->data;
    }


    /**
     * Overrides Zend_Service_Yahoo_ResultSet::current()
     *
     * @return Zend_Service_Yahoo_LocalResult
     */
    public function current()
    {
        return new Zend_Service_Yahoo_LocalResult($this->_results->item($this->_currentIndex));
    }
}
