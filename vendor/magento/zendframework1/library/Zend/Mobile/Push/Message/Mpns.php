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
 */

/** Zend_Mobile_Push_Message_Abstract **/
#require_once 'Zend/Mobile/Push/Message/Abstract.php';

/** Zend_Uri **/
#require_once 'Zend/Uri.php';

/**
 * Mpns Message
 *
 * @category   Zend
 * @package    Zend_Mobile
 * @subpackage Zend_Mobile_Push
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Mobile_Push_Message_Mpns extends Zend_Mobile_Push_Message_Abstract
{
    /**
     * Mpns types
     *
     * @var string
     */
    const TYPE_RAW = 'raw';
    const TYPE_TILE = 'token';
    const TYPE_TOAST = 'toast';

    /**
     * Delay
     *
     * @var int
     */
    protected $_delay;

    /**
     * Get Delay
     *
     * @return int
     */
    abstract public function getDelay();

    /**
     * Set Delay
     *
     * @param int $delay one of const DELAY_* of implementing classes
     * @return Zend_Mobile_Push_Message_Mpns
     */
    abstract public function setDelay($delay);

    /**
     * Get Notification Type
     *
     * @return string
     */
    public static function getNotificationType()
    {
        return "";
    }

    /**
     * Set Token
     *
     * @param string $token
     * @return Zend_Mobile_Push_Message_Mpns
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setToken($token)
    {
        if (!is_string($token)) {
            throw new Zend_Mobile_Push_Message_Exception('$token is not a string');
        }
        if (!Zend_Uri::check($token)) {
            throw new Zend_Mobile_Push_Message_Exception('$token is not a valid URI');
        }
        return parent::setToken($token);
    }

    /**
     * Get XML Payload
     *
     * @return string
     */
    abstract public function getXmlPayload();

    /**
     * Validate proper mpns message
     *
     * @return boolean
     */
    public function validate()
    {
        if (!isset($this->_token) || strlen($this->_token) === 0) {
            return false;
        }
        return parent::validate();
    }
}
