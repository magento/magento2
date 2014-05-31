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
 * @version    $Id: TearDownCall.php 20166 2010-01-09 19:00:17Z bkarwin $
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
class Zend_Service_DeveloperGarden_Request_VoiceButler_TearDownCall
    extends Zend_Service_DeveloperGarden_Request_VoiceButler_VoiceButlerAbstract
{
    /**
     * the session id
     *
     * @var string
     */
    public $sessionId = null;

    /**
     * constructor give them the environment and the sessionId
     *
     * @param integer $environment
     * @param string $sessionId
     * @return Zend_Service_DeveloperGarden_Request_RequestAbstract
     */
    public function __construct($environment, $sessionId)
    {
        parent::__construct($environment);
        $this->setSessionId($sessionId);
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * sets new sessionId
     *
     * @param string $sessionId
     * @return Zend_Service_DeveloperGarden_Request_VoiceButler_TearDownCall
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
        return $this;
    }
}
