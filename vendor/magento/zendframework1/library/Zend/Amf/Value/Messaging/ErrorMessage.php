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
 * @package    Zend_Amf
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** @see Zend_Amf_Value_Messaging_AcknowledgeMessage */
#require_once 'Zend/Amf/Value/Messaging/AcknowledgeMessage.php';

/**
 * Creates the error message to report to flex the issue with the call
 *
 * Corresponds to flex.messaging.messages.ErrorMessage
 *
 * @package    Zend_Amf
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Value_Messaging_ErrorMessage extends Zend_Amf_Value_Messaging_AcknowledgeMessage
{
    /**
     * Additional data with error
     * @var object
     */
    public $extendedData = null;

    /**
     * Error code number
     * @var string
     */
    public $faultCode;

    /**
     * Description as to the cause of the error
     * @var string
     */
    public $faultDetail;

    /**
     * Short description of error
     * @var string
     */
    public $faultString = '';

    /**
     * root cause of error
     * @var object
     */
    public $rootCause = null;
}
