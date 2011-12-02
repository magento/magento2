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
 * @version    $Id: AddConferenceTemplateParticipantRequest.php 20166 2010-01-09 19:00:17Z bkarwin $
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
class Zend_Service_DeveloperGarden_Request_ConferenceCall_AddConferenceTemplateParticipantRequest
    extends Zend_Service_DeveloperGarden_Request_RequestAbstract
{
    /**
     * the template id
     *
     * @var string
     */
    public $templateId = null;

    /**
     * the participant details
     *
     * @var Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail
     */
    public $participant = null;

    /**
     * constructor
     *
     * @param integer $environment
     * @param string $templateId
     * @param Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail $participant
     */
    public function __construct($environment, $templateId,
        Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail $participant = null
    ) {
        parent::__construct($environment);
        $this->setTemplateId($templateId)
             ->setParticipant($participant);
    }

    /**
     * set the template id
     *
     * @param string $templateId
     * @return Zend_Service_DeveloperGarden_Request_ConferenceCall_AddConferenceTemplateParticipantRequest
     */
    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;
        return $this;
    }

    /**
     * sets new participant
     *
     * @param Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail $participant
     * @return Zend_Service_DeveloperGarden_Request_ConferenceCall_AddConferenceTemplateParticipantRequest
     */
    public function setParticipant(Zend_Service_DeveloperGarden_ConferenceCall_ParticipantDetail $participant)
    {
        $this->participant = $participant;
        return $this;
    }
}
