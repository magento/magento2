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
 * @subpackage Parse_Amf0
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Amf_Constants */
#require_once 'Zend/Amf/Constants.php';

/** Zend_Xml_Security */
#require_once 'Zend/Xml/Security.php';

/** @see Zend_Amf_Parse_Deserializer */
#require_once 'Zend/Amf/Parse/Deserializer.php';

/**
 * Read an AMF0 input stream and convert it into PHP data types
 *
 * @todo       Implement Typed Object Class Mapping
 * @todo       Class could be implemented as Factory Class with each data type it's own class
 * @package    Zend_Amf
 * @subpackage Parse_Amf0
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Parse_Amf0_Deserializer extends Zend_Amf_Parse_Deserializer
{
    /**
     * An array of objects used for recursively deserializing an object.
     * @var array
     */
    protected $_reference = array();

    /**
     * If AMF3 serialization occurs, update to AMF0 0x03
     *
     * @var int
     */
    protected $_objectEncoding = Zend_Amf_Constants::AMF0_OBJECT_ENCODING;

    /**
     * Read AMF markers and dispatch for deserialization
     *
     * Checks for AMF marker types and calls the appropriate methods
     * for deserializing those marker types. Markers are the data type of
     * the following value.
     *
     * @param  integer $typeMarker
     * @return mixed whatever the data type is of the marker in php
     * @throws Zend_Amf_Exception for invalid type
     */
    public function readTypeMarker($typeMarker = null)
    {
        if ($typeMarker === null) {
            $typeMarker = $this->_stream->readByte();
        }

        switch($typeMarker) {
            // number
            case Zend_Amf_Constants::AMF0_NUMBER:
                return $this->_stream->readDouble();

            // boolean
            case Zend_Amf_Constants::AMF0_BOOLEAN:
                return (boolean) $this->_stream->readByte();

            // string
            case Zend_Amf_Constants::AMF0_STRING:
                return $this->_stream->readUTF();

            // object
            case Zend_Amf_Constants::AMF0_OBJECT:
                return $this->readObject();

            // null
            case Zend_Amf_Constants::AMF0_NULL:
                return null;

            // undefined
            case Zend_Amf_Constants::AMF0_UNDEFINED:
                return null;

            // Circular references are returned here
            case Zend_Amf_Constants::AMF0_REFERENCE:
                return $this->readReference();

            // mixed array with numeric and string keys
            case Zend_Amf_Constants::AMF0_MIXEDARRAY:
                return $this->readMixedArray();

            // array
            case Zend_Amf_Constants::AMF0_ARRAY:
                return $this->readArray();

            // date
            case Zend_Amf_Constants::AMF0_DATE:
                return $this->readDate();

            // longString  strlen(string) > 2^16
            case Zend_Amf_Constants::AMF0_LONGSTRING:
                return $this->_stream->readLongUTF();

            //internal AS object,  not supported
            case Zend_Amf_Constants::AMF0_UNSUPPORTED:
                return null;

            // XML
            case Zend_Amf_Constants::AMF0_XML:
                return $this->readXmlString();

            // typed object ie Custom Class
            case Zend_Amf_Constants::AMF0_TYPEDOBJECT:
                return $this->readTypedObject();

            //AMF3-specific
            case Zend_Amf_Constants::AMF0_AMF3:
                return $this->readAmf3TypeMarker();

            default:
                #require_once 'Zend/Amf/Exception.php';
                throw new Zend_Amf_Exception('Unsupported marker type: ' . $typeMarker);
        }
    }

    /**
     * Read AMF objects and convert to PHP objects
     *
     * Read the name value pair objects form the php message and convert them to
     * a php object class.
     *
     * Called when the marker type is 3.
     *
     * @param  array|null $object
     * @return object
     */
    public function readObject($object = null)
    {
        if ($object === null) {
            $object = array();
        }

        while (true) {
            $key        = $this->_stream->readUTF();
            $typeMarker = $this->_stream->readByte();
            if ($typeMarker != Zend_Amf_Constants::AMF0_OBJECTTERM ){
                //Recursivly call readTypeMarker to get the types of properties in the object
                $object[$key] = $this->readTypeMarker($typeMarker);
            } else {
                //encountered AMF object terminator
                break;
            }
        }
        $this->_reference[] = $object;
        return (object) $object;
    }

    /**
     * Read reference objects
     *
     * Used to gain access to the private array of reference objects.
     * Called when marker type is 7.
     *
     * @return object
     * @throws Zend_Amf_Exception for invalid reference keys
     */
    public function readReference()
    {
        $key = $this->_stream->readInt();
        if (!array_key_exists($key, $this->_reference)) {
            #require_once 'Zend/Amf/Exception.php';
            throw new Zend_Amf_Exception('Invalid reference key: '. $key);
        }
        return $this->_reference[$key];
    }

    /**
     * Reads an array with numeric and string indexes.
     *
     * Called when marker type is 8
     *
     * @todo   As of Flash Player 9 there is not support for mixed typed arrays
     *         so we handle this as an object. With the introduction of vectors
     *         in Flash Player 10 this may need to be reconsidered.
     * @return array
     */
    public function readMixedArray()
    {
        $length = $this->_stream->readLong();
        return $this->readObject();
    }

    /**
     * Converts numerically indexed actiosncript arrays into php arrays.
     *
     * Called when marker type is 10
     *
     * @return array
     */
    public function readArray()
    {
        $length = $this->_stream->readLong();
        $array = array();
        while ($length--) {
            $array[] = $this->readTypeMarker();
        }
        return $array;
    }

    /**
     * Convert AS Date to Zend_Date
     *
     * @return Zend_Date
     */
    public function readDate()
    {
        // get the unix time stamp. Not sure why ActionScript does not use
        // milliseconds
        $timestamp = floor($this->_stream->readDouble() / 1000);

        // The timezone offset is never returned to the server; it is always 0,
        // so read and ignore.
        $offset = $this->_stream->readInt();

        #require_once 'Zend/Date.php';
        $date   = new Zend_Date($timestamp);
        return $date;
    }

    /**
     * Convert XML to SimpleXml
     * If user wants DomDocument they can use dom_import_simplexml
     *
     * @return SimpleXml Object
     */
    public function readXmlString()
    {
        $string = $this->_stream->readLongUTF();
        return Zend_Xml_Security::scan($string); //simplexml_load_string($string);
    }

    /**
     * Read Class that is to be mapped to a server class.
     *
     * Commonly used for Value Objects on the server
     *
     * @todo   implement Typed Class mapping
     * @return object|array
     * @throws Zend_Amf_Exception if unable to load type
     */
    public function readTypedObject()
    {
         #require_once 'Zend/Amf/Parse/TypeLoader.php';
        // get the remote class name
        $className = $this->_stream->readUTF();
        $loader = Zend_Amf_Parse_TypeLoader::loadType($className);
        $returnObject = new $loader();
        $properties = get_object_vars($this->readObject());
        foreach($properties as $key=>$value) {
            if($key) {
                $returnObject->$key = $value;
            }
        }
        if($returnObject instanceof Zend_Amf_Value_Messaging_ArrayCollection) {
            $returnObject = get_object_vars($returnObject);
        }
        return $returnObject;
    }

    /**
     * AMF3 data type encountered load AMF3 Deserializer to handle
     * type markers.
     *
     * @return string
     */
    public function readAmf3TypeMarker()
    {
        #require_once 'Zend/Amf/Parse/Amf3/Deserializer.php';
        $deserializer = new Zend_Amf_Parse_Amf3_Deserializer($this->_stream);
        $this->_objectEncoding = Zend_Amf_Constants::AMF3_OBJECT_ENCODING;
        return $deserializer->readTypeMarker();
    }

    /**
     * Return the object encoding to check if an AMF3 object
     * is going to be return.
     *
     * @return int
     */
    public function getObjectEncoding()
    {
        return $this->_objectEncoding;
    }
}
