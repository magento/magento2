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
 * @version    $Id: VoiceButlerAbstract.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Service_DeveloperGarden_Response_ResponseAbstract
 */
#require_once 'Zend/Service/DeveloperGarden/Response/ResponseAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Service_DeveloperGarden_Response_VoiceButler_VoiceButlerAbstract
    extends Zend_Service_DeveloperGarden_Response_ResponseAbstract
{
    /**
     * the return from the sms request
     *
     * @var stdClass
     */
    public $return = null;

    /**
     * returns the return object
     *
     * @return stdClass
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * parse the response data and throws exceptions
     *
     * @throws Zend_Service_DeveloperGarden_Response_Exception
     * @return Zend_Service_DeveloperGarden_Response_ResponseAbstract
     */
    public function parse()
    {
        if ($this->hasError()) {
            throw new Zend_Service_DeveloperGarden_Response_Exception(
                $this->getErrorMessage(),
                $this->getErrorCode()
            );
        }

        return $this;
    }

    /**
     * returns the error code
     *
     * @return string|null
     */
    public function getErrorCode()
    {
        $retValue = null;
        if ($this->return instanceof stdClass) {
            $retValue = $this->return->status;
        }
        return $retValue;
    }

    /**
     * returns the error message
     *
     * @return string
     */
    public function getErrorMessage()
    {
        $retValue = null;
        if ($this->return instanceof stdClass) {
            $retValue = $this->return->err_msg;
        }
        return $retValue;
    }

    /**
     * returns true if the errorCode is not null and not 0000
     *
     * @return boolean
     */
    public function isValid()
    {
        return ($this->return === null
                || $this->return->status == '0000');
    }

    /**
     * returns true if we have a error situation
     *
     * @return boolean
     */
    public function hasError()
    {
        $retValue = false;
        if ($this->return instanceof stdClass
            && $this->return->status != '0000'
        ) {
            $retValue = true;
        }
        return $retValue;
    }
}
