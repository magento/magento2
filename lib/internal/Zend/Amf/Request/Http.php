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
 * @subpackage Request
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Http.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** @see Zend_Amf_Request */
#require_once 'Zend/Amf/Request.php';

/**
 * AMF Request object -- Request via HTTP
 *
 * Extends {@link Zend_Amf_Request} to accept a request via HTTP. Request is
 * built at construction time using a raw POST; if no data is available, the
 * request is declared a fault.
 *
 * @package    Zend_Amf
 * @subpackage Request
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Request_Http extends Zend_Amf_Request
{
    /**
     * Raw AMF request
     * @var string
     */
    protected $_rawRequest;

    /**
     * Constructor
     *
     * Attempts to read from php://input to get raw POST request; if an error
     * occurs in doing so, or if the AMF body is invalid, the request is declared a
     * fault.
     *
     * @return void
     */
    public function __construct()
    {
        // php://input allows you to read raw POST data. It is a less memory
        // intensive alternative to $HTTP_RAW_POST_DATA and does not need any
        // special php.ini directives
        $amfRequest = file_get_contents('php://input');

        // Check to make sure that we have data on the input stream.
        if ($amfRequest != '') {
            $this->_rawRequest = $amfRequest;
            $this->initialize($amfRequest);
        } else {
            echo '<p>Zend Amf Endpoint</p>' ;
        }
    }

    /**
     * Retrieve raw AMF Request
     *
     * @return string
     */
    public function getRawRequest()
    {
        return $this->_rawRequest;
    }
}
