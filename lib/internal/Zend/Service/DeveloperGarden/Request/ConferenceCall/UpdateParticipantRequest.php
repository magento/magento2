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
 * @version    $Id: UpdateParticipantRequest.php 20166 2010-01-09 19:00:17Z bkarwin $
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
class Zend_Service_DeveloperGarden_Request_ConferenceCall_UpdateParticipantRequest
    extends Zend_Service_DeveloperGarden_Request_RequestAbstract
{
    /**
     * the conference id
     *
     * @var string
     */
    public $conferenceId = null;

    /**
     * the participant id
     *
     * @var string
     */
    public $participantId = null;

    /**
     * conference participant
     *
     * @var Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail
     */
    public $participant = null;

    /**
     * possible action
     *
     * @var integer
     */
    public $action = null;

    /**
     * constructor
     *
     * @param integer $environment
     * @param string $conferenceId
     * @param string $participantId
     * @param integer $action
     * @param Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail $participant
     */
    public function __construct($environment, $conferenceId, $participantId,
        $action = null,
        Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail $participant = null
    ) {
        parent::__construct($environment);
        $this->setConferenceId($conferenceId)
             ->setParticipantId($participantId)
             ->setAction($action)
             ->setParticipant($participant);
    }

    /**
     * set the conference id
     *
     * @param string $conferenceId
     * @return Zend_Service_DeveloperGarden_Request_ConferenceCall_UpdateParticipantRequest
     */
    public function setConferenceId($conferenceId)
    {
        $this->conferenceId = $conferenceId;
        return $this;
    }

    /**
     * set the participant id
     *
     * @param string $participantId
     * @return Zend_Service_DeveloperGarden_Request_ConferenceCall_UpdateParticipantRequest
     */
    public function setParticipantId($participantId)
    {
        $this->participantId = $participantId;
        return $this;
    }

    /**
     * sets new action
     *
     * @param integer $action
     * @return Zend_Service_DeveloperGarden_Request_ConferenceCall_UpdateParticipantRequest
     */
    public function setAction($action = null)
    {
        if ($action !== null) {
            Zend_Service_DeveloperGarden_ConferenceCall::checkParticipantAction($action);
        }
        $this->action = $action;
        return $this;
    }

    /**
     * sets new participant
     *
     * @param Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail $participant
     * @return Zend_Service_DeveloperGarden_Request_ConferenceCall_UpdateParticipantRequest
     */
    public function setParticipant(
        Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail $participant = null
    ) {
        $this->participant = $participant;
        return $this;
    }
}
