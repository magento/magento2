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
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: SendSmsAbstract.php 20418 2010-01-19 11:43:30Z bate $
 */

/**
 * @see Zend_Service_DeveloperGarden_Request_RequestAbstract
 */
#require_once 'Zend/Service/DeveloperGarden/Request/RequestAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Service_DeveloperGarden_Request_SendSms_SendSmsAbstract
    extends Zend_Service_DeveloperGarden_Request_RequestAbstract
{
    /**
     * the number or numbers to receive this sms
     *
     * @var string
     */
    public $number = null;

    /**
     * the message of this sms
     *
     * @var string
     */
    public $message = null;

    /**
     * name of the sender
     *
     * @var string
     */
    public $originator = null;

    /**
     * account
     *
     * @var integer
     */
    public $account = null;

    /**
     * array of special chars that are used for counting
     * message length
     *
     * @var array
     */
    private $_specialChars = array(
        '|', 
        '^', 
        '{', 
        '}', 
        '[', 
        ']', 
        '~', 
        '\\', 
        "\n",
        // '€', removed because its counted in utf8 correctly
    );

    /**
     * what SMS type is it
     *
     * 1 = SMS
     * 2 = FlashSMS
     *
     * @var integer
     */
    protected $_smsType = 1;

    /**
     * the counter for increasing message count
     * if more than this 160 chars we send a 2nd or counting
     * sms message
     *
     * @var integer
     */
    protected $_smsLength = 153;

    /**
     * maximum length of an sms message
     *
     * @var integer
     */
    protected $_maxLength = 765;

    /**
     * the maximum numbers to send an sms
     *
     * @var integer
     */
    protected $_maxNumbers = 10;

    /**
     * returns the assigned numbers
     *
     * @return string $number
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * set a new number(s)
     *
     * @param string $number
     * @throws Zend_Service_DeveloperGarden_Request_Exception
     *
     * @return Zend_Service_DeveloperGarden_Request_SendSms_SendSmsAbstract
     */
    public function setNumber($number)
    {
        $this->number = $number;
        if ($this->getNumberCount() > $this->_maxNumbers) {
            #require_once 'Zend/Service/DeveloperGarden/Request/Exception.php';
            throw new Zend_Service_DeveloperGarden_Request_Exception('The message is too long.');
        }
        return $this;
    }

    /**
     * returns the current message
     *
     * @return string $message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * sets a new message
     *
     * @param string $message
     * @throws Zend_Service_DeveloperGarden_Request_Exception
     *
     * @return Zend_Service_DeveloperGarden_Request_SendSms_SendSmsAbstract
     */
    public function setMessage($message)
    {
        $this->message = $message;
        if ($this->getMessageLength() > $this->_maxLength) {
            #require_once 'Zend/Service/DeveloperGarden/Request/Exception.php';
            throw new Zend_Service_DeveloperGarden_Request_Exception('The message is too long.');
        }
        return $this;
    }

    /**
     * returns the originator
     *
     * @return the $originator
     */
    public function getOriginator()
    {
        return $this->originator;
    }

    /**
     * the originator name
     *
     * @param string $originator
     * @return Zend_Service_DeveloperGarden_Request_SendSms_SendSmsAbstract
     */
    public function setOriginator($originator)
    {
        $this->originator = $originator;
        return $this;
    }

    /**
     * the account
     * @return integer $account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * sets a new accounts
     *
     * @param $account the $account to set
     * @return Zend_Service_DeveloperGarden_Request_SendSms_SendSmsAbstract
     */
    public function setAccount($account)
    {
        $this->account = $account;
        return $this;
    }

    /**
     * returns the calculated message length
     *
     * @return integer
     */
    public function getMessageLength()
    {
        $message = $this->getMessage();
        $length  = strlen($message);

        foreach ($this->_specialChars as $char) {
            $c = (substr_count($message, $char) * 2) - 1;
            if ($c > 0) {
                $length += $c;
            }
        }

        return $length;
    }

    /**
     * returns the count of sms messages that would be send
     *
     * @return integer
     */
    public function getMessageCount()
    {
        $smsLength = $this->getMessageLength();
        $retValue = 1;
        if ($smsLength > 160) {
            $retValue = ceil($smsLength / $this->_smsLength);
        }
        return $retValue;
    }

    /**
     * returns the count of numbers in this sms
     *
     * @return integer
     */
    public function getNumberCount()
    {
        $number   = $this->getNumber();
        $retValue = 0;
        if (!empty($number)) {
            $retValue = count(explode(',', $number));
        }
        return $retValue;
    }

    /**
     * returns the sms type
     * currently we have
     * 1 = Sms
     * 2 = FlashSms
     *
     * @return integer
     */
    public function getSmsType()
    {
        return $this->_smsType;
    }
}
