<?php
/**
 * This file contains the code for an abstract transport layer.
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
 * @author     Dietrich Ayala <dietrich@ganx4.com>
 * @author     Shane Caraveo <Shane@Caraveo.com>
 * @author     Jan Schneider <jan@horde.org>
 * @copyright  2003-2006 The PHP Group
 * @license    http://www.php.net/license/2_02.txt  PHP License 2.02
 * @link       http://pear.php.net/package/SOAP
 */

require_once 'SOAP/Base.php';

/**
 * SOAP Transport Layer
 *
 * This layer can use different protocols dependant on the endpoint url
 * provided.
 *
 * No knowlege of the SOAP protocol is available at this level.
 * No knowlege of the transport protocols is available at this level.
 *
 * @access   public
 * @package  SOAP
 * @author   Shane Caraveo <shane@php.net>
 * @author   Jan Schneider <jan@horde.org>
 */
class SOAP_Transport extends SOAP_Base
{
    /**
     * Connection endpoint URL.
     *
     * @var string
     */
    var $url = '';

    /**
     * Array containing urlparts.
     *
     * @see parse_url()
     *
     * @var mixed
     */
    var $urlparts = null;

    /**
     * Incoming payload.
     *
     * @var string
     */
    var $incoming_payload = '';

    /**
     * Outgoing payload.
     *
     * @var string
     */
    var $outgoing_payload = '';

    /**
     * Request encoding.
     *
     * @var string
     */
    var $encoding = SOAP_DEFAULT_ENCODING;

    /**
     * Response encoding.
     *
     * We assume UTF-8 if no encoding is set.
     *
     * @var string
     */
    var $result_encoding = 'UTF-8';

    /**
     * Decoded attachments from the reponse.
     *
     * @var array
     */
    var $attachments;

    /**
     * Request User-Agent.
     *
     * @var string
     */
    var $_userAgent = SOAP_LIBRARY_NAME;

    /**
     * Sends and receives SOAP data.
     *
     * @access public
     * @abstract
     *
     * @param string  Outgoing SOAP data.
     * @param array   Options.
     *
     * @return string|SOAP_Fault
     */
    function send($msg, $options = null)
    {
        return $this->_raiseSoapFault('SOAP_Transport::send() not implemented.');
    }

    public static function getTransport($url, $encoding = SOAP_DEFAULT_ENCODING)
    {
        $urlparts = @parse_url($url);

        if (!$urlparts['scheme']) {
            return SOAP_Base_Object::_raiseSoapFault("Invalid transport URI: $url");
        }

        if (strcasecmp($urlparts['scheme'], 'mailto') == 0) {
            $transport_type = 'SMTP';
        } elseif (strcasecmp($urlparts['scheme'], 'https') == 0) {
            $transport_type = 'HTTP';
        } else {
            /* Handle other transport types */
            $transport_type = strtoupper($urlparts['scheme']);
        }
        $transport_class = "SOAP_Transport_$transport_type";
        if (!class_exists($transport_class)) {
            if (!(@include_once('SOAP/Transport/' . basename($transport_type) . '.php'))) {
                return SOAP_Base_Object::_raiseSoapFault("No Transport for {$urlparts['scheme']}");
            }
        }
        if (!class_exists($transport_class)) {
            return SOAP_Base_Object::_raiseSoapFault("No Transport class $transport_class");
        }

        return new $transport_class($url, $encoding);
    }

}
