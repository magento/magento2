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
 * @package    Zend_Crypt
 * @subpackage Rsa
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category   Zend
 * @package    Zend_Crypt
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Crypt_Rsa_Key implements Countable
{
    /**
     * @var string
     */
    protected $_pemString = null;

    /**
     * Bits, key string and type of key
     *
     * @var array
     */
    protected $_details = array();

    /**
     * Key Resource
     *
     * @var resource
     */
    protected $_opensslKeyResource = null;

    /**
     * Retrieves key resource
     *
     * @return resource
     */
    public function getOpensslKeyResource()
    {
        return $this->_opensslKeyResource;
    }

    /**
     * @return string
     * @throws Zend_Crypt_Exception
     */
    public function toString()
    {
        if (!empty($this->_pemString)) {
            return $this->_pemString;
        } elseif (!empty($this->_certificateString)) {
            return $this->_certificateString;
        }
        /**
         * @see Zend_Crypt_Exception
         */
        #require_once 'Zend/Crypt/Exception.php';
        throw new Zend_Crypt_Exception('No public key string representation is available');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    public function count()
    {
        return $this->_details['bits'];
    }

    public function getType()
    {
        return $this->_details['type'];
    }
}
