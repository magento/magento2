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
 * @subpackage Push
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id $
 */

/** Zend_Mobile_Push_Apns **/
#require_once 'Zend/Mobile/Push/Apns.php';

/**
 * Apns Test Proxy
 * This class is utilized for unit testing purposes
 *
 * @category   Zend
 * @package    Zend_Mobile
 * @subpackage Push
 */
class Zend_Mobile_Push_Test_ApnsProxy extends Zend_Mobile_Push_Apns
{
    /**
     * Read Response
     *
     * @var string
     */
    protected $_readResponse;

    /**
     * Write Response
     *
     * @var mixed
     */
    protected $_writeResponse;

    /**
     * Set the Response
     *
     * @param string $str
     */
    public function setReadResponse($str) {
        $this->_readResponse = $str;
    }

    /**
     * Set the write response
     *
     * @param mixed $resp
     * @return void
     */
    public function setWriteResponse($resp)
    {
        $this->_writeResponse = $resp;
    }

    /**
     * Connect
     *
     * @return true
     */
    protected function _connect($uri) {
        return true;
    }

    /**
     * Return Response
     *
     * @param string $length
     * @return string
     */
    protected function _read($length) {
        $ret = substr($this->_readResponse, 0, $length);
        $this->_readResponse = null;
        return $ret;
    }

    /**
     * Write and Return Length
     *
     * @param string $payload
     * @return int
     */
    protected function _write($payload) {
        $ret = $this->_writeResponse;
        $this->_writeResponse = null;
        return (null === $ret) ? strlen($payload) : $ret;
    }
}
