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

/** Zend_Amf_Constants */
#require_once 'Zend/Amf/Constants.php';


/** Zend_Amf_Parse_Serializer */
#require_once 'Zend/Amf/Parse/Serializer.php';

/** Zend_Amf_Parse_TypeLoader */
#require_once 'Zend/Amf/Parse/TypeLoader.php';

/**
 * Detect PHP object type and convert it to a corresponding AMF3 object type
 *
 * @package    Zend_Amf
 * @subpackage Parse_Amf3
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Parse_Amf3_Serializer extends Zend_Amf_Parse_Serializer
{
    /**
     * A constant empty string
     * @var string
     */
    protected $_strEmpty = '';

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
     * An array of reference class definitions, indexed by classname
     * @var array
     */
    protected $_referenceDefinitions = array();

    /**
     * Serialize PHP types to AMF3 and write to stream
     *
     * Checks to see if the type was declared and then either
     * auto negotiates the type or use the user defined markerType to
     * serialize the data from php back to AMF3
     *
     * @param  mixed $data
     * @param  int $markerType
     * @param  mixed $dataByVal
     * @return void
     */
    public function writeTypeMarker(&$data, $markerType = null, $dataByVal = false)
    {
        // Workaround for PHP5 with E_STRICT enabled complaining about "Only
        // variables should be passed by reference"
        if ((null === $data) && ($dataByVal !== false)) {
            $data = &$dataByVal;
        }
        if (null !== $markerType) {
            // Write the Type Marker to denote the following action script data type
            $this->_stream->writeByte($markerType);

            switch ($markerType) {
                case Zend_Amf_Constants::AMF3_NULL:
                    break;
                case Zend_Amf_Constants::AMF3_BOOLEAN_FALSE:
                    break;
                case Zend_Amf_Constants::AMF3_BOOLEAN_TRUE:
                    break;
                case Zend_Amf_Constants::AMF3_INTEGER:
                    $this->writeInteger($data);
                    break;
                case Zend_Amf_Constants::AMF3_NUMBER:
                    $this->_stream->writeDouble($data);
                    break;
                case Zend_Amf_Constants::AMF3_STRING:
                    $this->writeString($data);
                    break;
                case Zend_Amf_Constants::AMF3_DATE:
                    $this->writeDate($data);
                    break;
                case Zend_Amf_Constants::AMF3_ARRAY:
                    $this->writeArray($data);
                    break;
                case Zend_Amf_Constants::AMF3_OBJECT:
                    $this->writeObject($data);
                    break;
                case Zend_Amf_Constants::AMF3_BYTEARRAY:
                    $this->writeByteArray($data);
                    break;
                case Zend_Amf_Constants::AMF3_XMLSTRING;
                    $this->writeXml($data);
                    break;
                default:
                    #require_once 'Zend/Amf/Exception.php';
                    throw new Zend_Amf_Exception('Unknown Type Marker: ' . $markerType);
            }
        } else {
            // Detect Type Marker
            if (is_resource($data)) {
                $data = Zend_Amf_Parse_TypeLoader::handleResource($data);
            }
            switch (true) {
                case (null === $data):
                    $markerType = Zend_Amf_Constants::AMF3_NULL;
                    break;
                case (is_bool($data)):
                    if ($data){
                        $markerType = Zend_Amf_Constants::AMF3_BOOLEAN_TRUE;
                    } else {
                        $markerType = Zend_Amf_Constants::AMF3_BOOLEAN_FALSE;
                    }
                    break;
                case (is_int($data)):
                    if (($data > 0xFFFFFFF) || ($data < -268435456)) {
                        $markerType = Zend_Amf_Constants::AMF3_NUMBER;
                    } else {
                        $markerType = Zend_Amf_Constants::AMF3_INTEGER;
                    }
                    break;
                case (is_float($data)):
                    $markerType = Zend_Amf_Constants::AMF3_NUMBER;
                    break;
                case (is_string($data)):
                    $markerType = Zend_Amf_Constants::AMF3_STRING;
                    break;
                case (is_array($data)):
                    $markerType = Zend_Amf_Constants::AMF3_ARRAY;
                    break;
                case (is_object($data)):
                    // Handle object types.
                    if (($data instanceof DateTime) || ($data instanceof Zend_Date)) {
                        $markerType = Zend_Amf_Constants::AMF3_DATE;
                    } else if ($data instanceof Zend_Amf_Value_ByteArray) {
                        $markerType = Zend_Amf_Constants::AMF3_BYTEARRAY;
                    } else if (($data instanceof DOMDocument) || ($data instanceof SimpleXMLElement)) {
                        $markerType = Zend_Amf_Constants::AMF3_XMLSTRING;
                    } else {
                        $markerType = Zend_Amf_Constants::AMF3_OBJECT;
                    }
                    break;
                default:
                    #require_once 'Zend/Amf/Exception.php';
                    throw new Zend_Amf_Exception('Unsupported data type: ' . gettype($data));
            }
            $this->writeTypeMarker($data, $markerType);
        }
    }

    /**
     * Write an AMF3 integer
     *
     * @param int|float $data
     * @return Zend_Amf_Parse_Amf3_Serializer
     */
    public function writeInteger($int)
    {
        if (($int & 0xffffff80) == 0) {
            $this->_stream->writeByte($int & 0x7f);
            return $this;
        }

        if (($int & 0xffffc000) == 0 ) {
            $this->_stream->writeByte(($int >> 7 ) | 0x80);
            $this->_stream->writeByte($int & 0x7f);
            return $this;
        }

        if (($int & 0xffe00000) == 0) {
            $this->_stream->writeByte(($int >> 14 ) | 0x80);
            $this->_stream->writeByte(($int >> 7 ) | 0x80);
            $this->_stream->writeByte($int & 0x7f);
            return $this;
        }

        $this->_stream->writeByte(($int >> 22 ) | 0x80);
        $this->_stream->writeByte(($int >> 15 ) | 0x80);
        $this->_stream->writeByte(($int >> 8 ) | 0x80);
        $this->_stream->writeByte($int & 0xff);
        return $this;
    }

    /**
     * Send string to output stream, without trying to reference it.
     * The string is prepended with strlen($string) << 1 | 0x01
     *
     * @param  string $string
     * @return Zend_Amf_Parse_Amf3_Serializer
     */
    protected function writeBinaryString(&$string){
        $ref = ($this->_mbStringFunctionsOverloaded ? mb_strlen($string, '8bit') : strlen($string)) << 1 | 0x01;
        $this->writeInteger($ref);
        $this->_stream->writeBytes($string);

        return $this;
    }

    /**
     * Send string to output stream
     *
     * @param  string $string
     * @return Zend_Amf_Parse_Amf3_Serializer
     */
    public function writeString(&$string)
    {
        $len = $this->_mbStringFunctionsOverloaded ? mb_strlen($string, '8bit') : strlen($string);
        if(!$len){
            $this->writeInteger(0x01);
            return $this;
        }

        $ref = array_key_exists($string, $this->_referenceStrings)
             ? $this->_referenceStrings[$string]
             : false;
        if ($ref === false){
            $this->_referenceStrings[$string] = count($this->_referenceStrings);
            $this->writeBinaryString($string);
        } else {
            $ref <<= 1;
            $this->writeInteger($ref);
        }

        return $this;
    }

    /**
     * Send ByteArray to output stream
     *
     * @param  string|Zend_Amf_Value_ByteArray  $data
     * @return Zend_Amf_Parse_Amf3_Serializer
     */
    public function writeByteArray(&$data)
    {
        if ($this->writeObjectReference($data)) {
            return $this;
        }

        if (is_string($data)) {
            //nothing to do
        } else if ($data instanceof Zend_Amf_Value_ByteArray) {
            $data = $data->getData();
        } else {
            #require_once 'Zend/Amf/Exception.php';
            throw new Zend_Amf_Exception('Invalid ByteArray specified; must be a string or Zend_Amf_Value_ByteArray');
        }

        $this->writeBinaryString($data);

        return $this;
    }

    /**
     * Send xml to output stream
     *
     * @param  DOMDocument|SimpleXMLElement  $xml
     * @return Zend_Amf_Parse_Amf3_Serializer
     */
    public function writeXml($xml)
    {
        if ($this->writeObjectReference($xml)) {
            return $this;
        }

        if(is_string($xml)) {
            //nothing to do
        } else if ($xml instanceof DOMDocument) {
            $xml = $xml->saveXml();
        } else if ($xml instanceof SimpleXMLElement) {
            $xml = $xml->asXML();
        } else {
            #require_once 'Zend/Amf/Exception.php';
            throw new Zend_Amf_Exception('Invalid xml specified; must be a DOMDocument or SimpleXMLElement');
        }

        $this->writeBinaryString($xml);

        return $this;
    }

    /**
     * Convert DateTime/Zend_Date to AMF date
     *
     * @param  DateTime|Zend_Date $date
     * @return Zend_Amf_Parse_Amf3_Serializer
     */
    public function writeDate($date)
    {
        if ($this->writeObjectReference($date)) {
            return $this;
        }

        if ($date instanceof DateTime) {
            $dateString = $date->format('U') * 1000;
        } elseif ($date instanceof Zend_Date) {
            $dateString = $date->toString('U') * 1000;
        } else {
            #require_once 'Zend/Amf/Exception.php';
            throw new Zend_Amf_Exception('Invalid date specified; must be a string DateTime or Zend_Date object');
        }

        $this->writeInteger(0x01);
        // write time to stream minus milliseconds
        $this->_stream->writeDouble($dateString);
        return $this;
    }

    /**
     * Write a PHP array back to the amf output stream
     *
     * @param array $array
     * @return Zend_Amf_Parse_Amf3_Serializer
     */
    public function writeArray(&$array)
    {
        // arrays aren't reference here but still counted
        $this->_referenceObjects[] = $array;

        // have to seperate mixed from numberic keys.
        $numeric = array();
        $string  = array();
        foreach ($array as $key => &$value) {
            if (is_int($key)) {
                $numeric[] = $value;
            } else {
                $string[$key] = $value;
            }
        }

        // write the preamble id of the array
        $length = count($numeric);
        $id     = ($length << 1) | 0x01;
        $this->writeInteger($id);

        //Write the mixed type array to the output stream
        foreach($string as $key => &$value) {
            $this->writeString($key)
                 ->writeTypeMarker($value);
        }
        $this->writeString($this->_strEmpty);

        // Write the numeric array to ouput stream
        foreach($numeric as &$value) {
            $this->writeTypeMarker($value);
        }
        return $this;
    }

    /**
     * Check if the given object is in the reference table, write the reference if it exists,
     * otherwise add the object to the reference table
     *
     * @param mixed $object object reference to check for reference
     * @param mixed $objectByVal object to check for reference
     * @return Boolean true, if the reference was written, false otherwise
     */
    protected function writeObjectReference(&$object, $objectByVal = false)
    {
        // Workaround for PHP5 with E_STRICT enabled complaining about "Only
        // variables should be passed by reference"
        if ((null === $object) && ($objectByVal !== false)) {
            $object = &$objectByVal;
        }

        $hash = spl_object_hash($object);
        $ref = array_key_exists($hash, $this->_referenceObjects)
             ? $this->_referenceObjects[$hash]
             : false;

        // quickly handle object references
        if ($ref !== false){
            $ref <<= 1;
            $this->writeInteger($ref);
            return true;
        }
        $this->_referenceObjects[$hash] = count($this->_referenceObjects);
        return false;
    }

    /**
     * Write object to ouput stream
     *
     * @param  mixed $data
     * @return Zend_Amf_Parse_Amf3_Serializer
     */
    public function writeObject($object)
    {
        if($this->writeObjectReference($object)){
            return $this;
        }

        $className = '';

        //Check to see if the object is a typed object and we need to change
        switch (true) {
             // the return class mapped name back to actionscript class name.
            case ($className = Zend_Amf_Parse_TypeLoader::getMappedClassName(get_class($object))):
                break;

            // Check to see if the user has defined an explicit Action Script type.
            case isset($object->_explicitType):
                $className = $object->_explicitType;
                break;

            // Check if user has defined a method for accessing the Action Script type
            case method_exists($object, 'getASClassName'):
                $className = $object->getASClassName();
                break;

            // No return class name is set make it a generic object
            case ($object instanceof stdClass):
                $className = '';
                break;

             // By default, use object's class name
            default:
                $className = get_class($object);
                break;
        }

        $writeTraits = true;

        //check to see, if we have a corresponding definition
        if(array_key_exists($className, $this->_referenceDefinitions)){
            $traitsInfo    = $this->_referenceDefinitions[$className]['id'];
            $encoding      = $this->_referenceDefinitions[$className]['encoding'];
            $propertyNames = $this->_referenceDefinitions[$className]['propertyNames'];

            $traitsInfo = ($traitsInfo << 2) | 0x01;

            $writeTraits = false;
        } else {
            $propertyNames = array();

            if($className == ''){
                //if there is no className, we interpret the class as dynamic without any sealed members
                $encoding = Zend_Amf_Constants::ET_DYNAMIC;
            } else {
                $encoding = Zend_Amf_Constants::ET_PROPLIST;

                foreach($object as $key => $value) {
                    if( $key[0] != "_") {
                        $propertyNames[] = $key;
                    }
                }
            }

            $this->_referenceDefinitions[$className] = array(
                        'id'            => count($this->_referenceDefinitions),
                        'encoding'      => $encoding,
                        'propertyNames' => $propertyNames,
                    );

            $traitsInfo = Zend_Amf_Constants::AMF3_OBJECT_ENCODING;
            $traitsInfo |= $encoding << 2;
            $traitsInfo |= (count($propertyNames) << 4);
        }

        $this->writeInteger($traitsInfo);

        if($writeTraits){
            $this->writeString($className);
            foreach ($propertyNames as $value) {
                $this->writeString($value);
            }
        }

        try {
            switch($encoding) {
                case Zend_Amf_Constants::ET_PROPLIST:
                    //Write the sealed values to the output stream.
                    foreach ($propertyNames as $key) {
                        $this->writeTypeMarker($object->$key);
                    }
                    break;
                case Zend_Amf_Constants::ET_DYNAMIC:
                    //Write the sealed values to the output stream.
                    foreach ($propertyNames as $key) {
                        $this->writeTypeMarker($object->$key);
                    }

                    //Write remaining properties
                    foreach($object as $key => $value){
                        if(!in_array($key,$propertyNames) && $key[0] != "_"){
                            $this->writeString($key);
                            $this->writeTypeMarker($value);
                        }
                    }

                    //Write an empty string to end the dynamic part
                    $this->writeString($this->_strEmpty);
                    break;
                case Zend_Amf_Constants::ET_EXTERNAL:
                    #require_once 'Zend/Amf/Exception.php';
                    throw new Zend_Amf_Exception('External Object Encoding not implemented');
                    break;
                default:
                    #require_once 'Zend/Amf/Exception.php';
                    throw new Zend_Amf_Exception('Unknown Object Encoding type: ' . $encoding);
            }
        } catch (Exception $e) {
            #require_once 'Zend/Amf/Exception.php';
            throw new Zend_Amf_Exception('Unable to writeObject output: ' . $e->getMessage(), 0, $e);
        }

        return $this;
    }
}
