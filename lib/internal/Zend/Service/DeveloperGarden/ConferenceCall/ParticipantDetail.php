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
 * @version    $Id: ParticipantDetail.php 20166 2010-01-09 19:00:17Z bkarwin $
 */
/**
 * @see Zend_Validate_EmailAddress
 */
#require_once 'Zend/Validate/EmailAddress.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail
{
    /**
     * @var string
     */
    public $firstName = null;

    /**
     * @var string
     */
    public $lastName = null;

    /**
     * @var string
     */
    public $number = null;

    /**
     * @var string
     */
    public $email = null;

    /**
     * @var integer
     */
    public $flags = null;

    /**
     * constructor for participant object
     *
     * @param string $firstName
     * @param string $lastName
     * @param string $number
     * @param string $email
     * @param integer $isInitiator
     */
    public function __construct($firstName, $lastName, $number, $email, $isInitiator = false)
    {
        $this->setFirstName($firstName)
             ->setLastName($lastName)
             ->setNumber($number)
             ->setEmail($email)
             ->setFlags((int) $isInitiator);
    }

    /**
     * returns the value of $firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * sets $firstName
     *
     * @param string $firstName
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * returns the value of $lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * sets $lastName
     *
     * @param string $lastName
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * returns the value of $number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * sets $number
     *
     * @param string $number
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    /**
     * returns the value of $email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * sets $email
     *
     * @param string email
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail
     */
    public function setEmail($email)
    {
        $validator = new Zend_Validate_EmailAddress();

        if (!$validator->isValid($email)) {
            #require_once 'Zend/Service/DeveloperGarden/Exception.php';
            throw new Zend_Service_DeveloperGarden_Exception('Not a valid e-mail address.');
        }
        $this->email = $email;
        return $this;
    }

    /**
     * returns the value of $flags
     *
     * @return integer
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * sets $flags (ie, initiator flag)
     *
     * @param integer $flags
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;
        return $this;
    }
}
