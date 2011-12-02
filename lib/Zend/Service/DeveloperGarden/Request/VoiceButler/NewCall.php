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
 * @version    $Id: NewCall.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Service_DeveloperGarden_VoiceButler_VoiceButlerAbstract
 */
#require_once 'Zend/Service/DeveloperGarden/Request/VoiceButler/VoiceButlerAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_Request_VoiceButler_NewCall
    extends Zend_Service_DeveloperGarden_Request_VoiceButler_VoiceButlerAbstract
{
    /**
     * the first number to be called
     *
     * @var string
     */
    public $aNumber = null;

    /**
     * the second number to be called
     *
     * @var string
     */
    public $bNumber = null;

    /**
     * Calling Line Identity Restriction (CLIR) disabled for $aNumber
     *
     * @var boolean
     */
    public $privacyA = null;

    /**
     * Calling Line Identity Restriction (CLIR) disabled for $bNumber
     *
     * @var boolean
     */
    public $privacyB = null;

    /**
     * time in seconds to wait for $aNumber
     *
     * @var integer
     */
    public $expiration = null;

    /**
     * max duration for this call in seconds
     *
     * @var integer
     */
    public $maxDuration = null;

    /**
     * param not used right now
     *
     * @var string
     */
    public $greeter = null;

    /**
     * Account Id which will be pay for this call
     *
     * @var integer
     */
    public $account = null;

    /**
     * @return string
     */
    public function getANumber()
    {
        return $this->aNumber;
    }

    /**
     * @param string $aNumber
     * @return Zend_Service_DeveloperGarden_Request_VoiceButler_NewCall
     */
    public function setANumber($aNumber)
    {
        $this->aNumber = $aNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getBNumber()
    {
        return $this->bNumber;
    }

    /**
     * @param string $bNumber
     * @return Zend_Service_DeveloperGarden_Request_VoiceButler_NewCall
     */
    public function setBNumber($bNumber)
    {
        $this->bNumber = $bNumber;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getPrivacyA()
    {
        return $this->privacyA;
    }

    /**
     * @param boolean $privacyA
     * @return Zend_Service_DeveloperGarden_Request_VoiceButler_NewCall
     */
    public function setPrivacyA($privacyA)
    {
        $this->privacyA = $privacyA;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getPrivacyB()
    {
        return $this->privacyB;
    }

    /**
     * @param boolean $privacyB
     * @return Zend_Service_DeveloperGarden_Request_VoiceButler_NewCall
     */
    public function setPrivacyB($privacyB)
    {
        $this->privacyB = $privacyB;
        return $this;
    }

    /**
     * @return integer
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @param integer $expiration
     * @return Zend_Service_DeveloperGarden_Request_VoiceButler_NewCall
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
        return $this;
    }

    /**
     * @return integer
     */
    public function getMaxDuration()
    {
        return $this->maxDuration;
    }

    /**
     * @param integer $maxDuration
     * @return Zend_Service_DeveloperGarden_Request_VoiceButler_NewCall
     */
    public function setMaxDuration($maxDuration)
    {
        $this->maxDuration = $maxDuration;
        return $this;
    }

    /**
     * @return string
     */
    public function getGreeter()
    {
        return $this->greeter;
    }

    /**
     * @param string $greeter
     * @return Zend_Service_DeveloperGarden_Request_VoiceButler_NewCall
     */
    public function setGreeter($greeter)
    {
        $this->greeter = $greeter;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param integer $account
     * @return Zend_Service_DeveloperGarden_Request_VoiceButler_NewCall
     */
    public function setAccount($account)
    {
        $this->account = $account;
        return $this;
    }
}
