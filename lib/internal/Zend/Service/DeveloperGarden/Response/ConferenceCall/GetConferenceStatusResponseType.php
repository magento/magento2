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
 * @version    $Id: GetConferenceStatusResponseType.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Service_DeveloperGarden_Response_BaseType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/BaseType.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceStatusResponseType
    extends Zend_Service_DeveloperGarden_Response_BaseType
{
    /**
     * details
     *
     * @var Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail
     */
    public $detail = null;

    /**
     * conference starttime
     *
     * @var Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule
     */
    public $schedule = null;

    /**
     * the starttime as longint
     *
     * @var integer
     */
    public $startTime = null;

    /**
     * array of Zend_Service_DeveloperGarden_ConferenceCall_Participant
     *
     * @var array
     */
    public $participants = null;

    /**
     * the account object
     *
     * @var Zend_Service_DeveloperGarden_ConferenceCall_ConferenceAccount
     */
    public $acc = null;

    /**
     * returns the details object
     *
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * returns the starttime
     *
     * @return integer
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * returns the schedule object
     *
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * returns array with all participants
     * Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail
     *
     * @return array of Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail
     */
    public function getParticipants()
    {
        if ($this->participants instanceof Zend_Service_DeveloperGarden_ConferenceCall_Participant) {
            $this->participants = array(
                $this->participants
            );
        }
        return $this->participants;
    }

    /**
     * returns the participant object if found in the response
     *
     * @param string $participantId
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail
     */
    public function getParticipantById($participantId)
    {
        $participants = $this->getParticipants();
        if ($participants !== null) {
            foreach ($participants as $participant) {
                if (strcmp($participant->getParticipantId(), $participantId) == 0) {
                    return $participant;
                }
            }
        }
        return null;
    }

    /**
     * returns the conference account details
     *
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ConferenceAccount
     */
    public function getAccount()
    {
        return $this->acc;
    }
}
