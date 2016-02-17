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
 * @package    Zend_Cloud
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * Zend_Exception
 */
#require_once 'Zend/Exception.php';


/**
 * @category   Zend
 * @package    Zend_Cloud
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_Exception extends Zend_Exception
{
    /**
     * Exception for the underlying adapter
     *
     * @var Exception
     */
    protected $_clientException;

    public function __construct($message, $code = 0, $clientException = null)
    {
        $this->_clientException = $clientException;
        parent::__construct($message, $code, $clientException);
    }

    public function getClientException() {
        return $this->_getPrevious();
    }
}

