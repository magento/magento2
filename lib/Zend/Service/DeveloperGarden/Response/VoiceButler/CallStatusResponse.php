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
 * @version    $Id: CallStatusResponse.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Service_DeveloperGarden_Response_VoiceButler_VoiceButlerAbstract
 */
#require_once 'Zend/Service/DeveloperGarden/Response/VoiceButler/VoiceButlerAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_Response_VoiceButler_CallStatusResponse
    extends Zend_Service_DeveloperGarden_Response_VoiceButler_VoiceButlerAbstract
{
    /**
     * returns the session id
     * @return string
     */
    public function getSessionId()
    {
        if (isset($this->return->sessionId)) {
            return $this->return->sessionId;
        }
        return null;
    }

    /**
     * returns the connection time for participant a
     *
     * @return integer
     */
    public function getConnectionTimeA()
    {
        if (isset($this->return->connectiontimea)) {
            return $this->return->connectiontimea;
        }
        return null;
    }

    /**
     * returns the connection time for participant b
     *
     * @return integer
     */
    public function getConnectionTimeB()
    {
        if (isset($this->return->connectiontimeb)) {
            return $this->return->connectiontimeb;
        }
        return null;
    }

    /**
     * returns the description time for participant a
     *
     * @return string
     */
    public function getDescriptionA()
    {
        if (isset($this->return->descriptiona)) {
            return $this->return->descriptiona;
        }
        return null;
    }

    /**
     * returns the description time for participant b
     *
     * @return string
     */
    public function getDescriptionB()
    {
        if (isset($this->return->descriptionb)) {
            return $this->return->descriptionb;
        }
        return null;
    }

    /**
     * returns the reason time for participant a
     *
     * @return integer
     */
    public function getReasonA()
    {
        if (isset($this->return->reasona)) {
            return $this->return->reasona;
        }
        return null;
    }

    /**
     * returns the reason time for participant b
     *
     * @return integer
     */
    public function getReasonB()
    {
        if (isset($this->return->reasonb)) {
            return $this->return->reasonb;
        }
        return null;
    }

    /**
     * returns the state time for participant a
     *
     * @return string
     */
    public function getStateA()
    {
        if (isset($this->return->statea)) {
            return $this->return->statea;
        }
        return null;
    }

    /**
     * returns the state time for participant b
     *
     * @return string
     */
    public function getStateB()
    {
        if (isset($this->return->stateb)) {
            return $this->return->stateb;
        }
        return null;
    }
}
