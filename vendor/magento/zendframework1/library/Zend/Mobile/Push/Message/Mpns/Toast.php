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

/**
 * Mpns Toast Message
 *
 * @category   Zend
 * @package    Zend_Mobile
 * @subpackage Zend_Mobile_Push
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Mobile_Push_Message_Mpns_Toast extends Zend_Mobile_Push_Message_Mpns
{
    /**
     * Mpns delays
     *
     * @var int
     */
    const DELAY_IMMEDIATE = 2;
    const DELAY_450S = 12;
    const DELAY_900S = 22;

    /**
     * Title
     *
     * @var string
     */
    protected $_title;

    /**
     * Message
     *
     * @var string
     */
    protected $_msg;

    /**
     * Params
     *
     * @var string
     */
    protected $_params;

    /**
     * Get Title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Set Title
     *
     * @param string $title
     * @return Zend_Mobile_Push_Message_Mpns_Toast
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setTitle($title)
    {
        if (!is_string($title)) {
            throw new Zend_Mobile_Push_Message_Exception('$title must be a string');
        }
        $this->_title = $title;
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
     * Set Message
     *
     * @param string $msg
     * @return Zend_Mobile_Push_Message_Mpns_Toast
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setMessage($msg)
    {
        if (!is_string($msg)) {
            throw new Zend_Mobile_Push_Message_Exception('$msg must be a string');
        }
        $this->_msg = $msg;
        return $this;
    }

    /**
     * Get Params
     *
     * @return string
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Set Params
     *
     * @param string $params
     * @return Zend_Mobile_Push_Message_Mpns_Toast
     * @throws Zend_Mobile_Push_Message_Exception
     */
    public function setParams($params)
    {
        if (!is_string($params)) {
            throw new Zend_Mobile_Push_Message_Exception('$params must be a string');
        }
        $this->_params = $params;
        return $this;
    }

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
     * @return Zend_Mobile_Push_Message_Mpns_Toast
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
     * Get Notification Type
     *
     * @return string
     */
    public static function getNotificationType()
    {
        return 'toast';
    }

    /**
     * Get XML Payload
     *
     * @return string
     */
    public function getXmlPayload()
    {
        $ret = '<?xml version="1.0" encoding="utf-8"?>'
            . '<wp:Notification xmlns:wp="WPNotification">'
            . '<wp:Toast>'
            . '<wp:Text1>' . htmlspecialchars($this->_title) . '</wp:Text1>'
            . '<wp:Text2>' . htmlspecialchars($this->_msg) . '</wp:Text2>';
        if (!empty($this->_params)) {
            $ret .= '<wp:Param>' . htmlspecialchars($this->_params) . '</wp:Param>';
        }
        $ret .= '</wp:Toast>'
            . '</wp:Notification>';
        return $ret;
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
        if (empty($this->_title)) {
            return false;
        }
        if (empty($this->_msg)) {
            return false;
        }
        return parent::validate();
    }
}
