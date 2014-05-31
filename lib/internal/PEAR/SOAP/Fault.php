<?php
/**
 * This file contains the SOAP_Fault class, used for all error objects in this
 * package.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 2.02 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is available at
 * through the world-wide-web at http://www.php.net/license/2_02.txt.  If you
 * did not receive a copy of the PHP license and are unable to obtain it
 * through the world-wide-web, please send a note to license@php.net so we can
 * mail you a copy immediately.
 *
 * @category   Web Services
 * @package    SOAP
 * @author     Dietrich Ayala <dietrich@ganx4.com> Original Author
 * @author     Shane Caraveo <Shane@Caraveo.com>   Port to PEAR and more
 * @author     Chuck Hagenbuch <chuck@horde.org>   Maintenance
 * @author     Jan Schneider <jan@horde.org>       Maintenance
 * @copyright  2003-2006 The PHP Group
 * @license    http://www.php.net/license/2_02.txt  PHP License 2.02
 * @link       http://pear.php.net/package/SOAP
 */

/** PEAR_Error */
require_once 'PEAR.php';

/**
 * PEAR::Error wrapper used to match SOAP Faults to PEAR Errors
 *
 * SOAP_Fault can provide a complete backtrace of the error.  Revealing these
 * details in a public web services is a bad idea because it can be used by
 * attackers.  Thus you have to enable backtrace information in SOAP_Fault
 * responses by putting the following code in your script after your
 * "require_once 'SOAP/Server.php';" line:
 *
 * <code>
 * $backtrace =& PEAR::getStaticProperty('SOAP_Fault', 'backtrace');
 * $backtrace = true;
 * </code>
 *
 * @package  SOAP
 * @access   public
 * @author   Shane Caraveo <Shane@Caraveo.com>   Port to PEAR and more
 * @author   Dietrich Ayala <dietrich@ganx4.com> Original Author
 */
class SOAP_Fault extends PEAR_Error
{
    /**
     * Constructor.
     *
     * @param string $faultstring  Message string for fault.
     * @param mixed $faultcode     The faultcode.
     * @param mixed $faultactor
     * @param mixed $detail        @see PEAR_Error
     * @param array $mode          @see PEAR_Error
     * @param array $options       @see PEAR_Error
     */
    function SOAP_Fault($faultstring = 'unknown error', $faultcode = 'Client',
                        $faultactor = null, $detail = null, $mode = null,
                        $options = null)
    {
        parent::PEAR_Error($faultstring, $faultcode, $mode, $options, $detail);
        if ($faultactor) {
            $this->error_message_prefix = $faultactor;
        }
    }

    /**
     * Returns a SOAP XML message that can be sent as a server response.
     *
     * @return string
     */
    function message($encoding = SOAP_DEFAULT_ENCODING)
    {
        $msg = new SOAP_Base();
        $params = array();
        $params[] = new SOAP_Value('faultcode', 'QName', SOAP_BASE::SOAPENVPrefix().':' . $this->code);
        $params[] = new SOAP_Value('faultstring', 'string', $this->message);
        $params[] = new SOAP_Value('faultactor', 'anyURI', $this->error_message_prefix);
        if (PEAR::getStaticProperty('SOAP_Fault', 'backtrace') &&
            isset($this->backtrace)) {
            $params[] = new SOAP_Value('detail', 'string', $this->backtrace);
        } else {
            $params[] = new SOAP_Value('detail', 'string', $this->userinfo);
        }

        $methodValue = new SOAP_Value('{' . SOAP_ENVELOP . '}Fault', 'Struct', $params);
        $headers = null;
        return $msg->makeEnvelope($methodValue, $headers, $encoding);
    }

    /**
     * Returns a simple native PHP array containing the fault data.
     *
     * @return array
     */
    function getFault()
    {
        $fault = new stdClass();
        $fault->faultcode = $this->code;
        $fault->faultstring = $this->message;
        $fault->faultactor = $this->error_message_prefix;
        $fault->detail = $this->userinfo;
        return $fault;
    }

    /**
     * Returns the SOAP actor for the fault.
     *
     * @return string
     */
    function getActor()
    {
        return $this->error_message_prefix;
    }

    /**
     * Returns the fault detail.
     *
     * @return string
     */
    function getDetail()
    {
        return $this->userinfo;
    }

}
