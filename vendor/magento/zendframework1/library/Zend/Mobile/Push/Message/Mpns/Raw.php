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

/** Zend_Mobile_Push_Message_Mpns **/
#require_once 'Zend/Mobile/Push/Message/Mpns.php';

/** Zend_Xml_Security */
#require_once 'Zend/Xml/Security.php';

/**
 * Mpns Raw Message
 *
 * @category   Zend
 * @package    Zend_Mobile
 * @subpackage Zend_Mobile_Push
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Mobile_Push_Message_Mpns_Raw extends Zend_Mobile_Push_Message_Mpns
{
    /**
     * Mpns delays
     *
     * @var int
     */
    const DELAY_IMMEDIATE = 3;
    const DELAY_450S = 13;
    const DELAY_900S = 23;

    /**
     * Message
     *
     * @var string
     */
    protected $_msg;

    /**
     * Get Delay
     *
     * @return int
     */
    public function getDelay()
    {
        if (!$this->_delay) {
            return self::DELAY_IMMEDIATE;
        }
        return $this->_delay;
    }

    /**
     * Set Delay
     *
     * @param int $delay
     * @return Zend_Mobile_Push_Message_Mpns_Raw
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setDelay($delay)
    {
        if (!in_array($delay, array(
            self::DELAY_IMMEDIATE,
            self::DELAY_450S,
            self::DELAY_900S
        ))) {
            throw new Zend_Mobile_Push_Message_Exception('$delay must be one of the DELAY_* constants');
        }
        $this->_delay = $delay;
        return $this;
    }

    /**
     * Set Message
     *
     * @param string $msg XML string
     * @return Zend_Mobile_Push_Message_Mpns_Raw
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setMessage($msg)
    {
        if (!is_string($msg)) {
            throw new Zend_Mobile_Push_Message_Exception('$msg is not a string');
        }
        if (!Zend_Xml_Security::scan($msg)) {
            throw new Zend_Mobile_Push_Message_Exception('$msg is not valid xml');
        }
        $this->_msg = $msg;
        return $this;
    }

    /**
     * Get Message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_msg;
    }

    /**
     * Get Notification Type
     *
     * @return string
     */
    public static function getNotificationType()
    {
        return 'raw';
    }

    /**
     * Get XML Payload
     *
     * @return string
     */
    public function getXmlPayload()
    {
        return $this->_msg;
    }

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
        if (empty($this->_msg)) {
            return false;
        }
        return parent::validate();
    }
}
