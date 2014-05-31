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
 * @version    $Id: UpdateConferenceRequest.php 20166 2010-01-09 19:00:17Z bkarwin $
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
class Zend_Service_DeveloperGarden_Request_ConferenceCall_UpdateConferenceRequest
    extends Zend_Service_DeveloperGarden_Request_RequestAbstract
{
    /**
     * conference id
     *
     * @var string
     */
    public $conferenceId = null;

    /**
     * account to be used for this conference
     *
     * @var integer
     */
    public $account = null;

    /**
     * unique owner id
     *
     * @var string
     */
    public $ownerId = null;

    /**
     * object with details for this conference
     *
     * @var Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail
     */
    public $detail = null;

    /**
     * object with schedule for this conference
     *
     * @var Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule
     */
    public $schedule = null;

    /**
     * constructor
     *
     * @param integer $environment
     * @param string $conferenceId
     * @param string $ownerId
     * @param Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail $conferenceDetails
     * @param Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule $conferenceSchedule
     * @param integer $account
     */
    public function __construct($environment, $conferenceId, $ownerId = null,
        Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail $conferenceDetails = null,
        Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule $conferenceSchedule = null,
        $account = null
    ) {
        parent::__construct($environment);
        $this->setConferenceId($conferenceId)
             ->setOwnerId($ownerId)
             ->setDetail($conferenceDetails)
             ->setSchedule($conferenceSchedule)
             ->setAccount($account);
    }

    /**
     * sets $conferenceId
     *
     * @param string $conferenceId
     * @return Zend_Service_DeveloperGarden_Request_ConferenceCall_UpdateConferenceRequest
     */
    public function setConferenceId($conferenceId)
    {
        $this->conferenceId= $conferenceId;
        return $this;
    }

    /**
     * sets $schedule
     *
     * @param Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule $schedule
     * @return Zend_Service_DeveloperGarden_Request_ConferenceCall_CreateConferenceRequest
     */
    public function setSchedule(
        Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule $schedule = null
    ) {
        $this->schedule = $schedule;
        return $this;
    }

    /**
     * sets $detail
     *
     * @param Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail $detail
     * @return Zend_Service_DeveloperGarden_Request_ConferenceCall_CreateConferenceRequest
     */
    public function setDetail(
        Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail $detail = null
    ) {
        $this->detail = $detail;
        return $this;
    }

    /**
     * sets $ownerId
     *
     * @param string $ownerId
     * @return Zend_Service_DeveloperGarden_Request_ConferenceCall_CreateConferenceRequest
     */
    public function setOwnerId($ownerId = null)
    {
        $this->ownerId = $ownerId;
        return $this;
    }

    /**
     * sets $account
     *
     * @param $account
     * @return Zend_Service_DeveloperGarden_Request_ConferenceCall_CreateConferenceRequest
     */
    public function setAccount($account = null)
    {
        $this->account = $account;
        return $this;
    }
}
