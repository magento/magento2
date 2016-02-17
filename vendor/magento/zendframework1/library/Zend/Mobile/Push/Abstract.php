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
 * @package    Zend_Mobile
 * @subpackage Zend_Mobile_Push
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Mobile_Push_Interface **/
#require_once 'Zend/Mobile/Push/Interface.php';

/** Zend_Mobile_Push_Exception **/
#require_once 'Zend/Mobile/Push/Exception.php';

/**
 * Push Abstract
 *
 * @category   Zend
 * @package    Zend_Mobile
 * @subpackage Zend_Mobile_Push
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
abstract class Zend_Mobile_Push_Abstract implements Zend_Mobile_Push_Interface
{
    /**
     * Is Connected
     *
     * @var boolean
     */
    protected $_isConnected = false;

    /**
     * Connect to the Push Server
     *
     * @return Zend_Mobile_Push_Abstract
     */
    public function connect()
    {
        $this->_isConnected = true;
        return $this;
    }

    /**
     * Send a Push Message
     *
     * @param Zend_Mobile_Push_Message_Abstract $message
     * @return boolean
     * @throws DomainException
     */
    public function send(Zend_Mobile_Push_Message_Abstract $message)
    {
        if (!$this->_isConnected) {
            $this->connect();
        }
        return true;
    }

    /**
     * Close the Connection to the Push Server
     *
     * @return void
     */
    public function close()
    {
        $this->_isConnected = false;
    }

    /**
     * Is Connected
     *
     * @return boolean
     */
    public function isConnected()
    {
        return $this->_isConnected;
    }

    /**
     * Set Options
     *
     * @param array $options
     * @return Zend_Mobile_Push_Abstract
     * @throws Zend_Mobile_Push_Exception
     */
    public function setOptions(array $options)
    {
        foreach ($options as $k => $v) {
            $method = 'set' . ucwords($k);
            if (!method_exists($this, $method)) {
                throw new Zend_Mobile_Push_Exception('The method "' . $method . "' does not exist.");
            }
            $this->$method($v);
        }
        return $this;
    }
}
