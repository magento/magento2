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
 * @version    $Id: ConferenceCall.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Service_DeveloperGarden_Client_ClientAbstract
 */
#require_once 'Zend/Service/DeveloperGarden/Client/ClientAbstract.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_CreateConferenceRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/CreateConferenceRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_CreateConferenceResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/CreateConferenceResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_CreateConferenceResponseType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/CreateConferenceResponseType.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_NewParticipantRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/NewParticipantRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_NewParticipantResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/NewParticipantResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_NewParticipantResponseType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/NewParticipantResponseType.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_GetParticipantStatusRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/GetParticipantStatusRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_GetParticipantStatusResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/GetParticipantStatusResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_GetParticipantStatusResponseType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/GetParticipantStatusResponseType.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_UpdateParticipantRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/UpdateParticipantRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_UpdateParticipantResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/UpdateParticipantResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_RemoveParticipantRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/RemoveParticipantRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_RemoveParticipantResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/RemoveParticipantResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_GetConferenceListRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/GetConferenceListRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceListResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/GetConferenceListResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceListResponseType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/GetConferenceListResponseType.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_RemoveConferenceRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/RemoveConferenceRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_RemoveConferenceResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/RemoveConferenceResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_CCSResponseType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/CCSResponseType.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_GetConferenceStatusRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/GetConferenceStatusRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceStatusResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/GetConferenceStatusResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceStatusResponseType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/GetConferenceStatusResponseType.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_GetRunningConferenceRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/GetRunningConferenceRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_GetRunningConferenceResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/GetRunningConferenceResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_GetRunningConferenceResponseType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/GetRunningConferenceResponseType.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_GetConferenceTemplateListRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/GetConferenceTemplateListRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceTemplateListResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/GetConferenceTemplateListResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceTemplateListResponseType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/GetConferenceTemplateListResponseType.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_CreateConferenceTemplateRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/CreateConferenceTemplateRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_CreateConferenceTemplateResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/CreateConferenceTemplateResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_CreateConferenceTemplateResponseType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/CreateConferenceTemplateResponseType.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_GetConferenceTemplateRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/GetConferenceTemplateRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceTemplateResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/GetConferenceTemplateResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceTemplateResponseType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/GetConferenceTemplateResponseType.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_RemoveConferenceTemplateRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/RemoveConferenceTemplateRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_RemoveConferenceTemplateResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/RemoveConferenceTemplateResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_UpdateConferenceTemplateRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/UpdateConferenceTemplateRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_UpdateConferenceTemplateResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/UpdateConferenceTemplateResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_GetConferenceTemplateParticipantRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/GetConferenceTemplateParticipantRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceTemplateParticipantResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/GetConferenceTemplateParticipantResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceTemplateParticipantResponseType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/GetConferenceTemplateParticipantResponseType.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_RemoveConferenceTemplateParticipantRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/RemoveConferenceTemplateParticipantRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_RemoveConferenceTemplateParticipantResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/RemoveConferenceTemplateParticipantResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_UpdateConferenceTemplateParticipantRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/UpdateConferenceTemplateParticipantRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_UpdateConferenceTemplateParticipantResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/UpdateConferenceTemplateParticipantResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_AddConferenceTemplateParticipantRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/AddConferenceTemplateParticipantRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_AddConferenceTemplateParticipantResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/AddConferenceTemplateParticipantResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_AddConferenceTemplateParticipantResponseType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/AddConferenceTemplateParticipantResponseType.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_CommitConferenceRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/CommitConferenceRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_CommitConferenceResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/CommitConferenceResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_ConferenceCall_UpdateConferenceRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/ConferenceCall/UpdateConferenceRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_ConferenceCall_UpdateConferenceResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ConferenceCall/UpdateConferenceResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail
 */
#require_once 'Zend/Service/DeveloperGarden/ConferenceCall/ConferenceDetail.php';

/**
 * @see Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule
 */
#require_once 'Zend/Service/DeveloperGarden/ConferenceCall/ConferenceSchedule.php';

/**
 * @see Zend_Service_DeveloperGarden_ConferenceCall_Participant
 */
#require_once 'Zend/Service/DeveloperGarden/ConferenceCall/Participant.php';

/**
 * @see Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail
 */
#require_once 'Zend/Service/DeveloperGarden/ConferenceCall/ParticipantDetail.php';

/**
 * @see Zend_Service_DeveloperGarden_ConferenceCall_ParticipantStatus
 */
#require_once 'Zend/Service/DeveloperGarden/ConferenceCall/ParticipantStatus.php';

/**
 * @see Zend_Service_DeveloperGarden_ConferenceCall_ConferenceAccount
 */
#require_once 'Zend/Service/DeveloperGarden/ConferenceCall/ConferenceAccount.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_ConferenceCall
    extends Zend_Service_DeveloperGarden_Client_ClientAbstract
{
    /**
     * wsdl file
     *
     * @var string
     */
    protected $_wsdlFile = 'https://gateway.developer.telekom.com/p3gw-mod-odg-ccs/services/ccsPort?wsdl';

    /**
     * the local WSDL file
     *
     * @var string
     */
    protected $_wsdlFileLocal = 'Wsdl/ccsPort.wsdl';

    /**
     * Response, Request Classmapping
     *
     * @var array
     *
     */
    protected $_classMap = array(
        //Struct
        'ConferenceDetailStruct'  => 'Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail',
        'ConferenceAccStruct'     => 'Zend_Service_DeveloperGarden_ConferenceCall_ConferenceAccount',
        'ScheduleStruct'          => 'Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule',
        'ParticipantStruct'       => 'Zend_Service_DeveloperGarden_ConferenceCall_Participant',
        'ParticipantDetailStruct' => 'Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail',
        'ParticipantStatusStruct' => 'Zend_Service_DeveloperGarden_ConferenceCall_ParticipantStatus',

        //Responses
        'CCSResponseType' => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_CCSResponseType',

        //Conference
        'createConferenceResponse'         => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_CreateConferenceResponse',
        'createConferenceResponseType'     => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_CreateConferenceResponseType',
        'removeConferenceResponse'         => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_RemoveConferenceResponse',
        'commitConferenceResponse'         => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_CommitConferenceResponse',
        'updateConferenceResponse'         => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_UpdateConferenceResponse',
        'getConferenceStatusResponse'      => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceStatusResponse',
        'getConferenceStatusResponseType'  => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceStatusResponseType',
        'getRunningConferenceResponse'     => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_GetRunningConferenceResponse',
        'getRunningConferenceResponseType' => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_GetRunningConferenceResponseType',
        'getConferenceListResponse'        => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceListResponse',
        'getConferenceListResponseType'    => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceListResponseType',

        //Participant
        'newParticipantResponse'           => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_NewParticipantResponse',
        'newParticipantResponseType'       => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_NewParticipantResponseType',
        'removeParticipantResponse'        => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_RemoveParticipantResponse',
        'updateParticipantResponse'        => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_UpdateParticipantResponse',
        'getParticipantStatusResponse'     => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_GetParticipantStatusResponse',
        'getParticipantStatusResponseType' => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_GetParticipantStatusResponseType',

        //Templates
        'createConferenceTemplateResponse'             => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_CreateConferenceTemplateResponse',
        'createConferenceTemplateResponseType'         => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_CreateConferenceTemplateResponseType',
        'getConferenceTemplateResponse'                => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceTemplateResponse',
        'getConferenceTemplateResponseType'            => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceTemplateResponseType',
        'updateConferenceTemplateResponse'             => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_UpdateConferenceTemplateResponse',
        'removeConferenceTemplateResponse'             => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_RemoveConferenceTemplateResponse',
        'getConferenceTemplateListResponse'            => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceTemplateListResponse',
        'getConferenceTemplateListResponseType'        => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceTemplateListResponseType',
        'addConferenceTemplateParticipantResponse'     => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_AddConferenceTemplateParticipantResponse',
        'addConferenceTemplateParticipantResponseType' => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_AddConferenceTemplateParticipantResponseType',
        'getConferenceTemplateParticipantResponse'     => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceTemplateParticipantResponse',
        'getConferenceTemplateParticipantResponseType' => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceTemplateParticipantResponseType',
        'updateConferenceTemplateParticipantResponse'  => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_UpdateConferenceTemplateParticipantResponse',
        'removeConferenceTemplateParticipantResponse'  => 'Zend_Service_DeveloperGarden_Response_ConferenceCall_RemoveConferenceTemplateParticipantResponse',
    );

    /**
     * creates a new conference, ownerId should be between 3 and 39
     * chars
     *
     * @param string $ownerId
     * @param Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail $conferenceDetails
     * @param Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule $conferenceSchedule
     * @param integer $account
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_CreateConferenceResponseType
     */
    public function createConference($ownerId,
        Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail $conferenceDetails,
        Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule $conferenceSchedule = null,
        $account = null
    ) {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_CreateConferenceRequest(
            $this->getEnvironment(),
            $ownerId,
            $conferenceDetails,
            $conferenceSchedule,
            $account
        );

        $result = $this->getSoapClient()->createConference(array(
            'createConferenceRequest' => $request
        ));

        return $result->parse();
    }

    /**
     * commits the given conference
     *
     * @param string $conferenceId
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_CommitConferenceResponse
     */
    public function commitConference($conferenceId)
    {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_CommitConferenceRequest(
            $this->getEnvironment(),
            $conferenceId
        );

        $result = $this->getSoapClient()->commitConference(array(
            'commitConferenceRequest' => $request
        ));

        return $result->parse();
    }

    /**
     * updates a conference with the given parameter
     *
     * @param string $conferenceId
     * @param string $ownerId
     * @param Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail $conferenceDetails
     * @param Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule $conferenceSchedule
     * @param string $account
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_CCSResponseType
     */
    public function updateConference(
        $conferenceId,
        $ownerId = null,
        Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail $conferenceDetails = null,
        Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule $conferenceSchedule = null,
        $account = null
    ) {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_UpdateConferenceRequest(
            $this->getEnvironment(),
            $conferenceId,
            $ownerId,
            $conferenceDetails,
            $conferenceSchedule,
            $account
        );

        $result = $this->getSoapClient()->updateConference(array(
            'updateConferenceRequest' => $request
        ));

        return $result->parse();
    }

    /**
     * get conference status details
     *
     * @param string $conferenceId
     * @param integer $what
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceStatusResponseType
     */
    public function getConferenceStatus($conferenceId, $what = 0)
    {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_GetConferenceStatusRequest(
            $this->getEnvironment(),
            $conferenceId,
            $what
        );

        $result = $this->getSoapClient()->getConferenceStatus(array(
            'getConferenceStatusRequest' => $request
        ));

        return $result->parse();
    }

    /**
     * returns the conferenceId of the running conference instance for a planned
     * recurring conference or the current conferenceId
     *
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_GetRunningConferenceResponseType
     */
    public function getRunningConference($conferenceId)
    {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_GetRunningConferenceRequest(
            $this->getEnvironment(),
            $conferenceId
        );

        $result = $this->getSoapClient()->getRunningConference(array(
            'getRunningConferenceRequest' => $request
        ));

        return $result->parse();
    }

    /**
     * remove a conference
     *
     * @param string $conferenceId
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_CCSResponseType
     */
    public function removeConference($conferenceId)
    {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_RemoveConferenceRequest(
            $this->getEnvironment(),
            $conferenceId
        );

        $result = $this->getSoapClient()->removeConference(array(
            'removeConferenceRequest' => $request
        ));

        return $result->parse();
    }

    /**
     * returns a list of conferences
     *
     * @param integer $what
     * @param string $ownerId
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceListResponseType
     */
    public function getConferenceList($what = 0, $ownerId = null)
    {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_GetConferenceListRequest(
            $this->getEnvironment(),
            $what,
            $ownerId
        );

        $result = $this->getSoapClient()->getConferenceList(array(
            'getConferenceListRequest' => $request
        ));

        return $result->parse();
    }

    /**
     * adds a new participant to the given conference
     *
     * @param string $conferenceId
     * @param Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail $participant
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_NewParticipantResponseType
     */
    public function newParticipant(
        $conferenceId,
        Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail $participant
    ) {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_NewParticipantRequest(
            $this->getEnvironment(),
            $conferenceId,
            $participant
        );

        $result = $this->getSoapClient()->newParticipant(array(
            'newParticipantRequest' => $request
        ));

        return $result->parse();
    }

    /**
     * fetches the participant details for the given conferenceId
     *
     * @param string $conferenceId
     * @param string $participantId
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_GetParticipantStatusResponseType
     */
    public function getParticipantStatus($conferenceId, $participantId)
    {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_GetParticipantStatusRequest(
            $this->getEnvironment(),
            $conferenceId,
            $participantId
        );

        $result = $this->getSoapClient()->getParticipantStatus(array(
            'getParticipantStatusRequest' => $request
        ));

        return $result->parse();
    }

    /**
     * removes the given participant from the conference
     *
     * @param string $conferenceId
     * @param string $participantId
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_CCSResponseType
     */
    public function removeParticipant($conferenceId, $participantId)
    {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_RemoveParticipantRequest(
            $this->getEnvironment(),
            $conferenceId,
            $participantId
        );

        $result = $this->getSoapClient()->removeParticipant(array(
            'removeParticipantRequest' => $request
        ));

        return $result->parse();
    }

    /**
     * updates the participant in the given conference
     *
     * @param string $conferenceId
     * @param string $participantId
     * @param integer $action
     * @param Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail $participant
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_CCSResponseType
     */
    public function updateParticipant(
        $conferenceId,
        $participantId,
        $action = null,
        Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail $participant = null
    ) {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_UpdateParticipantRequest(
            $this->getEnvironment(),
            $conferenceId,
            $participantId,
            $action,
            $participant
        );

        $result = $this->getSoapClient()->updateParticipant(array(
            'updateParticipantRequest' => $request
        ));

        return $result->parse();
    }

    /**
     * creates a new conference template
     *
     * @param string $ownerId
     * @param Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail $conferenceDetails
     * @param array $participants
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_CreateConferenceTemplateResponseType
     */
    public function createConferenceTemplate(
        $ownerId,
        Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail $conferenceDetails,
        array $participants = null
    ) {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_CreateConferenceTemplateRequest(
            $this->getEnvironment(),
            $ownerId,
            $conferenceDetails,
            $participants
        );

        $result = $this->getSoapClient()->createConferenceTemplate(array(
            'createConferenceTemplateRequest' => $request
        ));

        return $result->parse();
    }

    /**
     * get a specific template
     *
     * @param string $templateId
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceTemplateResponseType
     */
    public function getConferenceTemplate($templateId)
    {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_GetConferenceTemplateRequest(
            $this->getEnvironment(),
            $templateId
        );

        $result = $this->getSoapClient()->getConferenceTemplate(array(
            'getConferenceTemplateRequest' => $request
        ));

        return $result->parse();
    }

    /**
     * updates a conference template
     *
     * @param string $templateId
     * @param string $initiatorId
     * @param Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail $conferenceDetails
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_CCSResponseType
     */
    public function updateConferenceTemplate(
        $templateId,
        $initiatorId = null,
        Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail $conferenceDetails = null
    ) {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_UpdateConferenceTemplateRequest(
            $this->getEnvironment(),
            $templateId,
            $initiatorId,
            $conferenceDetails
        );

        $result = $this->getSoapClient()->updateConferenceTemplate(array(
            'updateConferenceTemplateRequest' => $request
        ));

        return $result->parse();
    }

    /**
     * remove a conference template
     *
     * @param string $templateId
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_CCSResponseType
     */
    public function removeConferenceTemplate($templateId)
    {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_RemoveConferenceTemplateRequest(
            $this->getEnvironment(),
            $templateId
        );

        $result = $this->getSoapClient()->removeConferenceTemplate(array(
            'removeConferenceTemplateRequest' => $request
        ));

        return $result->parse();
    }

    /**
     * lists all available conference templates for the given owner
     *
     * @param string $ownerId
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceTemplateListResponseType
     */
    public function getConferenceTemplateList($ownerId)
    {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_GetConferenceTemplateListRequest(
            $this->getEnvironment(),
            $ownerId
        );

        $result = $this->getSoapClient()->getConferenceTemplateList(array(
            'getConferenceTemplateListRequest' => $request
        ));

        return $result->parse();
    }

    /**
     * adds a new participants to the template
     *
     * @param string $templateId
     * @param Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail $participant
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_AddConferenceTemplateParticipantResponseType
     */
    public function addConferenceTemplateParticipant(
        $templateId,
        Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail $participant
    ) {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_AddConferenceTemplateParticipantRequest(
            $this->getEnvironment(),
            $templateId,
            $participant
        );

        $result = $this->getSoapClient()->addConferenceTemplateParticipant(array(
            'addConferenceTemplateParticipantRequest' => $request
        ));

        return $result->parse();
    }

    /**
     * returns a praticipant for the given templateId
     *
     * @param string $templateId
     * @param string $participantId
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_GetConferenceTemplateParticipantResponseType
     */
    public function getConferenceTemplateParticipant($templateId, $participantId)
    {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_GetConferenceTemplateParticipantRequest(
            $this->getEnvironment(),
            $templateId,
            $participantId
        );

        $result = $this->getSoapClient()->getConferenceTemplateParticipant(array(
            'getConferenceTemplateParticipantRequest' => $request
        ));

        return $result->parse();
    }

    /**
     * updates the participants details
     *
     * @param string $templateId
     * @param string $participantId
     * @param Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail $participant
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_CCSResponseType
     */
    public function updateConferenceTemplateParticipant(
        $templateId,
        $participantId,
        Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail $participant
    ) {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_UpdateConferenceTemplateParticipantRequest(
            $this->getEnvironment(),
            $templateId,
            $participantId,
            $participant
        );

        $result = $this->getSoapClient()->updateConferenceTemplateParticipant(array(
            'updateConferenceTemplateParticipantRequest' => $request
        ));

        return $result->parse();
    }

    /**
     * removes a praticipant from the given templateId
     *
     * @param string $templateId
     * @param string $participantId
     * @return Zend_Service_DeveloperGarden_Response_ConferenceCall_CCSResponseType
     */
    public function removeConferenceTemplateParticipant($templateId, $participantId)
    {
        $request = new Zend_Service_DeveloperGarden_Request_ConferenceCall_RemoveConferenceTemplateParticipantRequest(
            $this->getEnvironment(),
            $templateId,
            $participantId
        );

        $result = $this->getSoapClient()->removeConferenceTemplateParticipant(array(
            'removeConferenceTemplateParticipantRequest' => $request
        ));

        return $result->parse();
    }
}
