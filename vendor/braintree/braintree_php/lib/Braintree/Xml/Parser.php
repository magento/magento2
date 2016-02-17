<?php

/**
 * Braintree XML Parser
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
/**
 * Parses incoming Xml into arrays using PHP's
 * built-in SimpleXML, and its extension via
 * Iterator, SimpleXMLIterator
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class Braintree_Xml_Parser
{

    private static $_xmlRoot;
    private static $_responseType;

    /**
     * sets up the SimpleXMLIterator and starts the parsing
     * @access public
     * @param string $xml
     * @return array array mapped to the passed xml
     */
    public static function arrayFromXml($xml)
    {
        // SimpleXML provides the root information on construct
        $iterator = new SimpleXMLIterator($xml);
        $xmlRoot = $iterator->getName();
        $type = $iterator->attributes()->type;

        self::$_xmlRoot = $iterator->getName();
        self::$_responseType = $type;

        // return the mapped array with the root element as the header
        $array = array($xmlRoot => self::_iteratorToArray($iterator));
        return Braintree_Util::delimiterToCamelCaseArray($array);
    }

    /**
     * processes SimpleXMLIterator objects recursively
     *
     * @access protected
     * @param object $iterator
     * @return array xml converted to array
     */
    private static function _iteratorToArray($iterator)
    {
        $xmlArray = array();
        $value = null;

        // rewind the iterator and check if the position is valid
        // if not, return the string it contains
        $iterator->rewind();
        if (!$iterator->valid()) {
            return self::_typecastXmlValue($iterator);
        }
        for ($iterator->rewind(); $iterator->valid(); $iterator->next()) {

            $tmpArray = null;
            $value = null;

            // get the attribute type string for use in conditions below
            $attributeType = $iterator->attributes()->type;

            // extract the parent element via xpath query
            $parentElement = $iterator->xpath($iterator->key() . '/..');
            if ($parentElement[0] instanceof SimpleXMLIterator) {
                $parentElement = $parentElement[0];
            } else {
                $parentElement = null;
            }

            $key = $iterator->key();

            // process children recursively
            if ($iterator->hasChildren()) {
                // return the child elements
                $value = self::_iteratorToArray($iterator->current());

                // if the element is an array type,
                // use numeric keys to allow multiple values
                if ($attributeType != 'array') {
                    $tmpArray[$key] = $value;
                }
            } else {
                // cast values according to attributes
                $tmpArray[$key] = self::_typecastXmlValue($iterator->current());
            }

            // set the output string
            $output = isset($value) ? $value : $tmpArray[$key];

            // determine if there are multiple tags of this name at the same level
            if (isset($parentElement) &&
                ($parentElement->attributes()->type == 'collection') &&
                $iterator->hasChildren()) {
              $xmlArray[$key][] = $output;
              continue;
            }

            // if the element was an array type, output to a numbered key
            // otherwise, use the element name
            if ($attributeType == 'array') {
                $xmlArray[] = $output;
            } else {
                $xmlArray[$key] = $output;
            }
        }

        return $xmlArray;
    }

    /**
     * typecast xml value based on attributes
     * @param object $valueObj SimpleXMLElement
     * @return mixed value for placing into array
     */
    private static function _typecastXmlValue($valueObj)
    {
        // get the element attributes
        $attribs = $valueObj->attributes();
        // the element is null, so jump out here
        if (isset($attribs->nil) && $attribs->nil) {
            return null;
        }
        // switch on the type attribute
        // switch works even if $attribs->type isn't set
        switch ($attribs->type) {
            case 'datetime':
                return self::_timestampToUTC((string) $valueObj);
                break;
            case 'date':
                return new DateTime((string)$valueObj);
                break;
            case 'integer':
                return (int) $valueObj;
                break;
            case 'boolean':
                $value =  (string) $valueObj;
                // look for a number inside the string
                if(is_numeric($value)) {
                    return (bool) $value;
                } else {
                    // look for the string "true", return false in all other cases
                    return ($value != "true") ? FALSE : TRUE;
                }
                break;
            case 'array':
                return array();
            default:
                return (string) $valueObj;
        }

    }

    /**
     * convert xml timestamps into DateTime
     * @param string $timestamp
     * @return string UTC formatted datetime string
     */
    private static function _timestampToUTC($timestamp)
    {
        $tz = new DateTimeZone('UTC');
        // strangely DateTime requires an explicit set below
        // to show the proper time zone
        $dateTime = new DateTime($timestamp, $tz);
        $dateTime->setTimezone($tz);
        return $dateTime;
    }
}
