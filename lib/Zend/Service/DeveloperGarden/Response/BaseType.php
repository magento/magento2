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
 * @version    $Id: BaseType.php 20166 2010-01-09 19:00:17Z bkarwin $
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
class Zend_Service_DeveloperGarden_Response_BaseType
    extends Zend_Service_DeveloperGarden_Response_ResponseAbstract
{
    /**
     * the status code
     *
     * @var string
     */
    public $statusCode = null;

    /**
     * the status message
     *
     * @var string
     */
    public $statusMessage = null;

    /**
     * parse the result
     *
     * @throws Zend_Service_DeveloperGarden_Response_Exception
     * @return Zend_Service_DeveloperGarden_Response_ResponseAbstract
     */
    public function parse()
    {
        if ($this->hasError()) {
            throw new Zend_Service_DeveloperGarden_Response_Exception(
                $this->getStatusMessage(),
                $this->getStatusCode()
            );
        }

        return $this;
    }

    /**
     * returns the error code
     *
     * @return string|null
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * returns the error message
     *
     * @return string
     */
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    /**
     * returns true if the errorCode is not null and not 0000
     *
     * @return boolean
     */
    public function isValid()
    {
        return ($this->statusCode === null
             || $this->statusCode == '0000');
    }

    /**
     * returns true if we have a error situation
     *
     * @return boolean
     */
    public function hasError()
    {
        return ($this->statusCode !== null
             && $this->statusCode != '0000');
    }

    /**
     * returns the error code (statusCode)
     *
     * @return string|null
     */
    public function getErrorCode()
    {
        if (empty($this->errorCode)) {
            return $this->statusCode;
        } else {
            return $this->errorCode;
        }
    }

    /**
     * returns the error message
     *
     * @return string
     */
    public function getErrorMessage()
    {
        if (empty($this->errorMessage)) {
            return $this->statusMessage;
        } else {
            return $this->errorMessage;
        }
    }
}
