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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @see Zend_Service_Yahoo_ResultSet
 */
#require_once 'Zend/Service/Yahoo/ResultSet.php';


/**
 * @see Zend_Service_Yahoo_WebResult
 */
#require_once 'Zend/Service/Yahoo/InlinkDataResult.php';


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Yahoo
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Yahoo_InlinkDataResultSet extends Zend_Service_Yahoo_ResultSet
{
    /**
     * Web result set namespace
     *
     * @var string
     */
    protected $_namespace = 'urn:yahoo:srch';


    /**
     * Overrides Zend_Service_Yahoo_ResultSet::current()
     *
     * @return Zend_Service_Yahoo_InlinkDataResult
     */
    public function current()
    {
        return new Zend_Service_Yahoo_InlinkDataResult($this->_results->item($this->_currentIndex));
    }
}
