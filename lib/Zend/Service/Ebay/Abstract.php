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
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 22824 2010-08-09 18:59:54Z renanbr $
 */

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Service_Ebay_Abstract
{
    const OPTION_APP_ID    = 'app_id';
    const OPTION_GLOBAL_ID = 'global_id';

    /**
     * @var array
     */
    protected $_options = array();

    /**
     * @var mixed
     */
    protected $_client;

    /**
     * @param  Zend_Config|array $options
     * @return void
     */
    public function __construct($options = null)
    {
        $options = self::optionsToArray($options);
        $this->setOption($options);
    }

    /**
     * @param  string|Zend_Config|array $name
     * @param  mixed                    $value
     * @return Zend_Service_Ebay_Abstract Provides a fluent interface
     */
    public function setOption($name, $value = null)
    {
        if ($name instanceof Zend_Config) {
            $name = $name->toArray();
        }
        if (is_array($name)) {
            $this->_options = $name + $this->_options;
        } else {
            $this->_options[$name] = $value;
        }
        return $this;
    }

    /**
     * @param  string $name
     * @return mixed
     */
    public function getOption($name = null)
    {
        if (null === $name) {
            return $this->_options;
        }
        if ($this->hasOption($name)) {
            return $this->_options[$name];
        }
        return null;
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasOption($name)
    {
        return array_key_exists($name, $this->_options);
    }

    /**
     * @param  mixed $client
     * @return Zend_Service_Ebay_Abstract Provides a fluent interface
     */
    abstract public function setClient($client);

    /**
     * @return mixed
     */
    abstract public function getClient();

    /**
     * @param  Zend_Config|array $options
     * @throws Zend_Service_Ebay_Finding_Exception When $options is not an array neither a Zend_Config object
     * @return array
     */
    public static function optionsToArray($options)
    {
        if (null === $options) {
            $options = array();
        } else if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (!is_array($options)) {
            /**
             * @see Zend_Service_Ebay_Exception
             */
            #require_once 'Zend/Service/Ebay/Exception.php';
            throw new Zend_Service_Ebay_Exception('Invalid options provided.');
        }

        return $options;
    }

    /**
     * Implements Name-value Syntax translator.
     *
     * Example:
     *
     * array(
     *     'paginationInput' => array(
     *         'entriesPerPage' => 5,
     *         'pageNumber'     => 2
     *     ),
     *     'itemFilter' => array(
     *         array(
     *             'name'       => 'MaxPrice',
     *             'value'      => 25,
     *             'paramName'  => 'Currency',
     *             'paramValue' => 'USD'
     *         ),
     *         array(
     *             'name'  => 'FreeShippingOnly',
     *             'value' => true
     *         ),
     *         array(
     *             'name'  => 'ListingType',
     *             'value' => array(
     *                 'AuctionWithBIN',
     *                 'FixedPrice',
     *                 'StoreInventory'
     *             )
     *         )
     *     ),
     *     'productId' => array(
     *         ''     => 123,
     *         'type' => 'UPC'
     *     )
     * )
     *
     * this above is translated to
     *
     * array(
     *     'paginationInput.entriesPerPage' => '5',
     *     'paginationInput.pageNumber'     => '2',
     *     'itemFilter(0).name'             => 'MaxPrice',
     *     'itemFilter(0).value'            => '25',
     *     'itemFilter(0).paramName'        => 'Currency',
     *     'itemFilter(0).paramValue'       => 'USD',
     *     'itemFilter(1).name'             => 'FreeShippingOnly',
     *     'itemFilter(1).value'            => '1',
     *     'itemFilter(2).name'             => 'ListingType',
     *     'itemFilter(2).value(0)'         => 'AuctionWithBIN',
     *     'itemFilter(2).value(1)'         => 'FixedPrice',
     *     'itemFilter(2).value(2)'         => 'StoreInventory',
     *     'productId'                      => '123',
     *     'productId.@type'                => 'UPC'
     * )
     *
     * @param  Zend_Config|array $options
     * @link   http://developer.ebay.com/DevZone/finding/Concepts/MakingACall.html#nvsyntax
     * @return array A simple array of strings
     */
    protected function _optionsToNameValueSyntax($options)
    {
        $options  = self::optionsToArray($options);
        ksort($options);
        $new      = array();
        $runAgain = false;
        foreach ($options as $name => $value) {
            if (is_array($value)) {
                // parse an array value, check if it is associative
                $keyRaw    = array_keys($value);
                $keyNumber = range(0, count($value) - 1);
                $isAssoc   = count(array_diff($keyRaw, $keyNumber)) > 0;
                // check for tag representation, like <name att="sometinhg"></value>
                // empty key refers to text value
                // when there is a root tag, attributes receive flags
                $hasAttribute = array_key_exists('', $value);
                foreach ($value as $subName => $subValue) {
                    // generate new key name
                    if ($isAssoc) {
                        // named keys
                        $newName = $name;
                        if ($subName !== '') {
                            // when $subName is empty means that current value
                            // is the main value for the main key
                            $glue     = $hasAttribute ? '.@' : '.';
                            $newName .= $glue . $subName;
                        }
                    } else {
                        // numeric keys
                        $newName = $name . '(' . $subName . ')';
                    }
                    // save value
                    if (is_array($subValue)) {
                        // it is necessary run this again, value is an array
                        $runAgain = true;
                    } else {
                        // parse basic type
                        $subValue = self::toEbayValue($subValue);
                    }
                    $new[$newName] = $subValue;
                }
            } else {
                // parse basic type
                $new[$name] = self::toEbayValue($value);
            }
        }
        if ($runAgain) {
            // this happens if any $subValue found is an array
            $new = $this->_optionsToNameValueSyntax($new);
        }
        return $new;
    }

    /**
     * Translate native PHP values format to ebay format for request.
     *
     * Boolean is translated to "0" or "1", date object generates ISO 8601,
     * everything else is translated to string.
     *
     * @param  mixed $value
     * @return string
     */
    public static function toEbayValue($value)
    {
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        } else if ($value instanceof Zend_Date) {
            $value = $value->getIso();
        } else if ($value instanceof DateTime) {
            $value = $value->format(DateTime::ISO8601);
        } else {
            $value = (string) $value;
        }
        return $value;
    }

    /**
     * Translate an ebay value format to native PHP type.
     *
     * @param  string $value
     * @param  string $type
     * @see    http://developer.ebay.com/DevZone/finding/CallRef/types/simpleTypes.html
     * @throws Zend_Service_Ebay_Finding_Exception When $type is not valid
     * @return mixed
     */
    public static function toPhpValue($value, $type)
    {
        switch ($type) {
            // cast for: boolean
            case 'boolean':
                $value = (string) $value == 'true';
                break;

            // cast for: Amount, decimal, double, float, MeasureType
            case 'float':
                $value = floatval((string) $value);
                break;

            // cast for: int, long
            // integer type generates a string value, because 32 bit systems
            // have an integer range of -2147483648 to 2147483647
            case 'integer':
                // break intentionally omitted

            // cast for: anyURI, base64Binary, dateTime, duration, string, token
            case 'string':
                $value = (string) $value;
                break;

            default:
                /**
                 * @see Zend_Service_Ebay_Exception
                 */
                #require_once 'Zend/Service/Ebay/Exception.php';
                throw new Zend_Service_Ebay_Exception("Invalid type '{$type}'.");
        }
        return $value;
    }
}
