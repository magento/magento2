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

/** Zend_Mobile_Push_Message_Interface **/
#require_once 'Zend/Mobile/Push/Message/Interface.php';

/** Zend_Mobile_Push_Message_Exception **/
#require_once 'Zend/Mobile/Push/Message/Exception.php';

/**
 * Message Abstract
 *
 * @category   Zend
 * @package    Zend_Mobile
 * @subpackage Zend_Mobile_Push_Message
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
abstract class Zend_Mobile_Push_Message_Abstract implements Zend_Mobile_Push_Message_Interface
{
    /**
     * Token
     *
     * @var string
     */
    protected $_token;

    /**
     * Id
     *
     * @var int|string|float|bool Scalar
     */
    protected $_id;

    /**
     * Get Token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * Set Token
     *
     * @param  string $token
     * @throws Zend_Mobile_Push_Message_Exception
     * @return Zend_Mobile_Push_Message_Abstract
     */
    public function setToken($token)
    {
        if (!is_string($token)) {
            throw new Zend_Mobile_Push_Message_Exception('$token must be a string');
        }
        $this->_token = $token;
        return $this;
    }

    /**
     * Get Message ID
     * 
     * @return int|string|float|bool Scalar
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Set Message ID
     *
     * @param  int|string|float|bool $id Scalar
     * @return Zend_Mobile_Push_Message_Abstract
     * @throws Exception
     */
    public function setId($id)
    {
        if (!is_scalar($id)) {
            throw new Zend_Mobile_Push_Message_Exception('$id must be a scalar');
        }
        $this->_id = $id;
        return $this;
    }

    /**
     * Set Options
     *
     * @param array $options
     * @return Zend_Mobile_Push_Message_Abstract
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setOptions(array $options)
    {
        foreach ($options as $k => $v) {
            $method = 'set' . ucwords($k);
            if (!method_exists($this, $method)) {
                throw new Zend_Mobile_Push_Message_Exception('The method "' . $method . "' does not exist.");
            }
            $this->$method($v);
        }
        return $this;
    }


    /**
     * Validate Message format
     *
     * @return boolean
     */
    public function validate()
    {
        return true;
    }
}
