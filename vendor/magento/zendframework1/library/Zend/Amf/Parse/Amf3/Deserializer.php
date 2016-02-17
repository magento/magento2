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
 * @subpackage Parse_Amf3
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Amf_Parse_Deserializer */
#require_once 'Zend/Amf/Parse/Deserializer.php';

/** Zend_Xml_Security */
#require_once 'Zend/Xml/Security.php';

/** Zend_Amf_Parse_TypeLoader */
#require_once 'Zend/Amf/Parse/TypeLoader.php';

/**
 * Read an AMF3 input stream and convert it into PHP data types.
 *
 * @todo       readObject to handle Typed Objects
 * @todo       readXMLStrimg to be implemented.
 * @todo       Class could be implemented as Factory Class with each data type it's own class.
 * @package    Zend_Amf
 * @subpackage Parse_Amf3
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Parse_Amf3_Deserializer extends Zend_Amf_Parse_Deserializer
{
    /**
     * Total number of objects in the referenceObject array
     * @var int
     */
    protected $_objectCount;

    /**
     * An array of reference objects per amf body
     * @var array
     */
    protected $_referenceObjects = array();

    /**
     * An array of reference strings per amf body
     * @var array
     */
    protected $_referenceStrings = array();

    /**
     * An array of reference class definitions per body
     * @var array
     */
    protected $_referenceDefinitions = array();

    /**
     * Read AMF markers and dispatch for deserialization
     *
     * Checks for AMF marker types and calls the appropriate methods
     * for deserializing those marker types. markers are the data type of
     * the following value.
     *
     * @param  integer $typeMarker
     * @return mixed Whatever the corresponding PHP data type is
     * @throws Zend_Amf_Exception for unidentified marker type
     */
    public function readTypeMarker($typeMarker = null)
    {
        if(null === $typeMarker) {
            $typeMarker = $this->_stream->readByte();
        }

        switch($typeMarker) {
            case Zend_Amf_Constants::AMF3_UNDEFINED:
                 return null;
            case Zend_Amf_Constants::AMF3_NULL:
                 return null;
            case Zend_Amf_Constants::AMF3_BOOLEAN_FALSE:
                 return false;
            case Zend_Amf_Constants::AMF3_BOOLEAN_TRUE:
                 return true;
            case Zend_Amf_Constants::AMF3_INTEGER:
                 return $this->readInteger();
            case Zend_Amf_Constants::AMF3_NUMBER:
                 return $this->_stream->readDouble();
            case Zend_Amf_Constants::AMF3_STRING:
                 return $this->readString();
            case Zend_Amf_Constants::AMF3_DATE:
                 return $this->readDate();
            case Zend_Amf_Constants::AMF3_ARRAY:
                 return $this->readArray();
            case Zend_Amf_Constants::AMF3_OBJECT:
                 return $this->readObject();
            case Zend_Amf_Constants::AMF3_XML:
            case Zend_Amf_Constants::AMF3_XMLSTRING:
                 return $this->readXmlString();
            case Zend_Amf_Constants::AMF3_BYTEARRAY:
                 return $this->readString();
            default:
                #require_once 'Zend/Amf/Exception.php';
                throw new Zend_Amf_Exception('Unsupported type marker: ' . $typeMarker);
        }
    }

    /**
     * Read and deserialize an integer
     *
     * AMF 3 represents smaller integers with fewer bytes using the most
     * significant bit of each byte. The worst case uses 32-bits
     * to represent a 29-bit number, which is what we would have
     * done with no compression.
     * - 0x00000000 - 0x0000007F : 0xxxxxxx
     * - 0x00000080 - 0x00003FFF : 1xxxxxxx 0xxxxxxx
     * - 0x00004000 - 0x001FFFFF : 1xxxxxxx 1xxxxxxx 0xxxxxxx
     * - 0x00200000 - 0x3FFFFFFF : 1xxxxxxx 1xxxxxxx 1xxxxxxx xxxxxxxx
     * - 0x40000000 - 0xFFFFFFFF : throw range exception
     *
     * 0x04 -> integer type code, followed by up to 4 bytes of data.
     *
     * Parsing integers on OSFlash for the AMF3 integer data format:
     * @link http://osflash.org/amf3/parsing_integers
     * @return int|float
     */
    public function readInteger()
    {
        $count        = 1;
        $intReference = $this->_stream->readByte();
        $result       = 0;
        while ((($intReference & 0x80) != 0) && $count < 4) {
            $result       <<= 7;
            $result        |= ($intReference & 0x7f);
            $intReference   = $this->_stream->readByte();
            $count++;
        }
        if ($count < 4) {
            $result <<= 7;
            $result  |= $intReference;
        } else {
            // Use all 8 bits from the 4th byte
            $result <<= 8;
            $result  |= $intReference;

            // Check if the integer should be negative
            if (($result & 0x10000000) != 0) {
                //and extend the sign bit
                $result |= ~0xFFFFFFF;
            }
        }
        return $result;
    }

    /**
     * Read and deserialize a string
     *
     * Strings can be sent as a reference to a previously
     * occurring String by using an index to the implicit string reference table.
     * Strings are encoding using UTF-8 - however the header may either
     * describe a string literal or a string reference.
     *
     * - string = 0x06 string-data
     * - string-data = integer-data [ modified-utf-8 ]
     * - modified-utf-8 = *OCTET
     *
     * @return String
     */
    public function readString()
    {
        $stringReference = $this->readInteger();

        //Check if this is a reference string
        if (($stringReference & 0x01) == 0) {
            // reference string
            $stringReference = $stringReference >> 1;
            if ($stringReference >= count($this->_referenceStrings)) {
                #require_once 'Zend/Amf/Exception.php';
                throw new Zend_Amf_Exception('Undefined string reference: ' . $stringReference);
            }
            // reference string found
            return $this->_referenceStrings[$stringReference];
        }

        $length = $stringReference >> 1;
        if ($length) {
            $string = $this->_stream->readBytes($length);
            $this->_referenceStrings[] = $string;
        } else {
            $string = "";
        }
        return $string;
    }

    /**
     * Read and deserialize a date
     *
     * Data is the number of milliseconds elapsed since the epoch
     * of midnight, 1st Jan 1970 in the UTC time zone.
     * Local time zone information is not sent to flash.
     *
     * - date = 0x08 integer-data [ number-data ]
     *
     * @return Zend_Date
     */
    public function readDate()
    {
        $dateReference = $this->readInteger();
        if (($dateReference & 0x01) == 0) {
            $dateReference = $dateReference >> 1;
            if ($dateReference>=count($this->_referenceObjects)) {
                #require_once 'Zend/Amf/Exception.php';
                throw new Zend_Amf_Exception('Undefined date reference: ' . $dateReference);
            }
            return $this->_referenceObjects[$dateReference];
        }

        $timestamp = floor($this->_stream->readDouble() / 1000);

        #require_once 'Zend/Date.php';
        $dateTime  = new Zend_Date($timestamp);
        $this->_referenceObjects[] = $dateTime;
        return $dateTime;
    }

    /**
     * Read amf array to PHP array
     *
     * - array = 0x09 integer-data ( [ 1OCTET *amf3-data ] | [OCTET *amf3-data 1] | [ OCTET *amf-data ] )
     *
     * @return array
     */
    public function readArray()
    {
        $arrayReference = $this->readInteger();
        if (($arrayReference & 0x01)==0){
            $arrayReference = $arrayReference >> 1;
            if ($arrayReference>=count($this->_referenceObjects)) {
                #require_once 'Zend/Amf/Exception.php';
                throw new Zend_Amf_Exception('Unknow array reference: ' . $arrayReference);
            }
            return $this->_referenceObjects[$arrayReference];
        }

        // Create a holder for the array in the reference list
        $data = array();
        $this->_referenceObjects[] =& $data;
        $key = $this->readString();

        // Iterating for string based keys.
        while ($key != '') {
            $data[$key] = $this->readTypeMarker();
            $key = $this->readString();
        }

        $arrayReference = $arrayReference >>1;

        //We have a dense array
        for ($i=0; $i < $arrayReference; $i++) {
            $data[] = $this->readTypeMarker();
        }

        return $data;
    }

    /**
     * Read an object from the AMF stream and convert it into a PHP object
     *
     * @todo   Rather than using an array of traitsInfo create Zend_Amf_Value_TraitsInfo
     * @return object|array
     */
    public function readObject()
    {
        $traitsInfo   = $this->readInteger();
        $storedObject = ($traitsInfo & 0x01)==0;
        $traitsInfo   = $traitsInfo >> 1;

        // Check if the Object is in the stored Objects reference table
        if ($storedObject) {
            $ref = $traitsInfo;
            if (!isset($this->_referenceObjects[$ref])) {
                #require_once 'Zend/Amf/Exception.php';
                throw new Zend_Amf_Exception('Unknown Object reference: ' . $ref);
            }
            $returnObject = $this->_referenceObjects[$ref];
        } else {
            // Check if the Object is in the stored Definitions reference table
            $storedClass = ($traitsInfo & 0x01) == 0;
            $traitsInfo  = $traitsInfo >> 1;
            if ($storedClass) {
                $ref = $traitsInfo;
                if (!isset($this->_referenceDefinitions[$ref])) {
                    #require_once 'Zend/Amf/Exception.php';
                    throw new Zend_Amf_Exception('Unknows Definition reference: '. $ref);
                }
                // Populate the reference attributes
                $className     = $this->_referenceDefinitions[$ref]['className'];
                $encoding      = $this->_referenceDefinitions[$ref]['encoding'];
                $propertyNames = $this->_referenceDefinitions[$ref]['propertyNames'];
            } else {
                // The class was not in the reference tables. Start reading rawdata to build traits.
                // Create a traits table. Zend_Amf_Value_TraitsInfo would be ideal
                $className     = $this->readString();
                $encoding      = $traitsInfo & 0x03;
                $propertyNames = array();
                $traitsInfo    = $traitsInfo >> 2;
            }

            // We now have the object traits defined in variables. Time to go to work:
            if (!$className) {
                // No class name generic object
                $returnObject = new stdClass();
            } else {
                // Defined object
                // Typed object lookup against registered classname maps
                if ($loader = Zend_Amf_Parse_TypeLoader::loadType($className)) {
                    $returnObject = new $loader();
                } else {
                    //user defined typed object
                    #require_once 'Zend/Amf/Exception.php';
                    throw new Zend_Amf_Exception('Typed object not found: '. $className . ' ');
                }
            }

            // Add the Object to the reference table
            $this->_referenceObjects[] = $returnObject;

            $properties = array(); // clear value
            // Check encoding types for additional processing.
            switch ($encoding) {
                case (Zend_Amf_Constants::ET_EXTERNAL):
                    // Externalizable object such as {ArrayCollection} and {ObjectProxy}
                    if (!$storedClass) {
                        $this->_referenceDefinitions[] = array(
                            'className'     => $className,
                            'encoding'      => $encoding,
                            'propertyNames' => $propertyNames,
                        );
                    }
                    $returnObject->externalizedData = $this->readTypeMarker();
                    break;
                case (Zend_Amf_Constants::ET_DYNAMIC):
                    // used for Name-value encoding
                    if (!$storedClass) {
                        $this->_referenceDefinitions[] = array(
                            'className'     => $className,
                            'encoding'      => $encoding,
                            'propertyNames' => $propertyNames,
                        );
                    }
                    // not a reference object read name value properties from byte stream
                    do {
                        $property = $this->readString();
                        if ($property != "") {
                            $propertyNames[]       = $property;
                            $properties[$property] = $this->readTypeMarker();
                        }
                    } while ($property !="");
                    break;
                default:
                    // basic property list object.
                    if (!$storedClass) {
                        $count = $traitsInfo; // Number of properties in the list
                        for($i=0; $i< $count; $i++) {
                            $propertyNames[] = $this->readString();
                        }
                        // Add a reference to the class.
                        $this->_referenceDefinitions[] = array(
                            'className'     => $className,
                            'encoding'      => $encoding,
                            'propertyNames' => $propertyNames,
                        );
                    }
                    foreach ($propertyNames as $property) {
                        $properties[$property] = $this->readTypeMarker();
                    }
                    break;
            }

            // Add properties back to the return object.
            if (!is_array($properties)) $properties = array();
            foreach($properties as $key=>$value) {
                if($key) {
                    $returnObject->$key = $value;
                }
            }


        }

       if ($returnObject instanceof Zend_Amf_Value_Messaging_ArrayCollection) {
            if (isset($returnObject->externalizedData)) {
                $returnObject = $returnObject->externalizedData;
            } else {
                $returnObject = get_object_vars($returnObject);
            }
       }

        return $returnObject;
    }

    /**
     * Convert XML to SimpleXml
     * If user wants DomDocument they can use dom_import_simplexml
     *
     * @return SimpleXml Object
     */
    public function readXmlString()
    {
        $xmlReference = $this->readInteger();
        $length = $xmlReference >> 1;
        $string = $this->_stream->readBytes($length);
        return Zend_Xml_Security::scan($string);
    }
}
