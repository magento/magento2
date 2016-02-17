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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** @see Zend_Amf_Parse_InputStream */
#require_once 'Zend/Amf/Parse/InputStream.php';

/** @see Zend_Amf_Parse_Amf0_Deserializer */
#require_once 'Zend/Amf/Parse/Amf0/Deserializer.php';

/** @see Zend_Amf_Constants */
#require_once 'Zend/Amf/Constants.php';

/** @see Zend_Amf_Value_MessageHeader */
#require_once 'Zend/Amf/Value/MessageHeader.php';

/** @see Zend_Amf_Value_MessageBody */
#require_once 'Zend/Amf/Value/MessageBody.php';

/**
 * Handle the incoming AMF request by deserializing the data to php object
 * types and storing the data for Zend_Amf_Server to handle for processing.
 *
 * @todo       Currently not checking if the object needs to be Type Mapped to a server object.
 * @package    Zend_Amf
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Request
{
    /**
     * @var int AMF client type (AMF0, AMF3)
     */
    protected $_clientType = 0; // default AMF0

    /**
     * @var array Message bodies
     */
    protected $_bodies = array();

    /**
     * @var array Message headers
     */
    protected $_headers = array();

    /**
     * @var int Message encoding to use for objects in response
     */
    protected $_objectEncoding = 0;

    /**
     * @var Zend_Amf_Parse_InputStream
     */
    protected $_inputStream;

    /**
     * @var Zend_Amf_Parse_AMF0_Deserializer
     */
    protected $_deserializer;

    /**
     * Time of the request
     * @var  mixed
     */
    protected $_time;

    /**
     * Prepare the AMF InputStream for parsing.
     *
     * @param  string $request
     * @return Zend_Amf_Request
     */
    public function initialize($request)
    {
        $this->_inputStream  = new Zend_Amf_Parse_InputStream($request);
        $this->_deserializer = new Zend_Amf_Parse_Amf0_Deserializer($this->_inputStream);
        $this->readMessage($this->_inputStream);
        return $this;
    }

    /**
     * Takes the raw AMF input stream and converts it into valid PHP objects
     *
     * @param  Zend_Amf_Parse_InputStream
     * @return Zend_Amf_Request
     */
    public function readMessage(Zend_Amf_Parse_InputStream $stream)
    {
        $clientVersion = $stream->readUnsignedShort();
        if (($clientVersion != Zend_Amf_Constants::AMF0_OBJECT_ENCODING)
            && ($clientVersion != Zend_Amf_Constants::AMF3_OBJECT_ENCODING)
            && ($clientVersion != Zend_Amf_Constants::FMS_OBJECT_ENCODING)
        ) {
            #require_once 'Zend/Amf/Exception.php';
            throw new Zend_Amf_Exception('Unknown Player Version ' . $clientVersion);
        }

        $this->_bodies  = array();
        $this->_headers = array();
        $headerCount    = $stream->readInt();

        // Iterate through the AMF envelope header
        while ($headerCount--) {
            $this->_headers[] = $this->readHeader();
        }

        // Iterate through the AMF envelope body
        $bodyCount = $stream->readInt();
        while ($bodyCount--) {
            $this->_bodies[] = $this->readBody();
        }

        return $this;
    }

    /**
     * Deserialize a message header from the input stream.
     *
     * A message header is structured as:
     * - NAME String
     * - MUST UNDERSTAND Boolean
     * - LENGTH Int
     * - DATA Object
     *
     * @return Zend_Amf_Value_MessageHeader
     */
    public function readHeader()
    {
        $name     = $this->_inputStream->readUTF();
        $mustRead = (bool)$this->_inputStream->readByte();
        $length   = $this->_inputStream->readLong();

        try {
            $data = $this->_deserializer->readTypeMarker();
        } catch (Exception $e) {
            #require_once 'Zend/Amf/Exception.php';
            throw new Zend_Amf_Exception('Unable to parse ' . $name . ' header data: ' . $e->getMessage() . ' '. $e->getLine(), 0, $e);
        }

        $header = new Zend_Amf_Value_MessageHeader($name, $mustRead, $data, $length);
        return $header;
    }

    /**
     * Deserialize a message body from the input stream
     *
     * @return Zend_Amf_Value_MessageBody
     */
    public function readBody()
    {
        $targetURI   = $this->_inputStream->readUTF();
        $responseURI = $this->_inputStream->readUTF();
        $length      = $this->_inputStream->readLong();

        try {
            $data = $this->_deserializer->readTypeMarker();
        } catch (Exception $e) {
            #require_once 'Zend/Amf/Exception.php';
            throw new Zend_Amf_Exception('Unable to parse ' . $targetURI . ' body data ' . $e->getMessage(), 0, $e);
        }

        // Check for AMF3 objectEncoding
        if ($this->_deserializer->getObjectEncoding() == Zend_Amf_Constants::AMF3_OBJECT_ENCODING) {
            /*
             * When and AMF3 message is sent to the server it is nested inside
             * an AMF0 array called Content. The following code gets the object
             * out of the content array and sets it as the message data.
             */
            if(is_array($data) && $data[0] instanceof Zend_Amf_Value_Messaging_AbstractMessage){
                $data = $data[0];
            }

            // set the encoding so we return our message in AMF3
            $this->_objectEncoding = Zend_Amf_Constants::AMF3_OBJECT_ENCODING;
        }

        $body = new Zend_Amf_Value_MessageBody($targetURI, $responseURI, $data);
        return $body;
    }

    /**
     * Return an array of the body objects that were found in the amf request.
     *
     * @return array {target, response, length, content}
     */
    public function getAmfBodies()
    {
        return $this->_bodies;
    }

    /**
     * Accessor to private array of message bodies.
     *
     * @param  Zend_Amf_Value_MessageBody $message
     * @return Zend_Amf_Request
     */
    public function addAmfBody(Zend_Amf_Value_MessageBody $message)
    {
        $this->_bodies[] = $message;
        return $this;
    }

    /**
     * Return an array of headers that were found in the amf request.
     *
     * @return array {operation, mustUnderstand, length, param}
     */
    public function getAmfHeaders()
    {
        return $this->_headers;
    }

    /**
     * Return the either 0 or 3 for respect AMF version
     *
     * @return int
     */
    public function getObjectEncoding()
    {
        return $this->_objectEncoding;
    }

    /**
     * Set the object response encoding
     *
     * @param  mixed $int
     * @return Zend_Amf_Request
     */
    public function setObjectEncoding($int)
    {
        $this->_objectEncoding = $int;
        return $this;
    }
}
