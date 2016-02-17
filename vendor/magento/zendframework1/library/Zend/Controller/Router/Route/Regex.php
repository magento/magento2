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
 * @package    Zend_Controller
 * @subpackage Router
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id$
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Controller_Router_Route_Abstract */
#require_once 'Zend/Controller/Router/Route/Abstract.php';

/**
 * Regex Route
 *
 * @package    Zend_Controller
 * @subpackage Router
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Router_Route_Regex extends Zend_Controller_Router_Route_Abstract
{

    /**
     * Regex string
     *
     * @var string|null
     */
    protected $_regex = null;

    /**
     * Default values for the route (ie. module, controller, action, params)
     *
     * @var array
     */
    protected $_defaults = array();

    /**
     * Reverse
     *
     * @var string|null
     */
    protected $_reverse = null;

    /**
     * Map
     *
     * @var array
     */
    protected $_map = array();

    /**
     * Values
     *
     * @var array
     */
    protected $_values = array();

    /**
     * Instantiates route based on passed Zend_Config structure
     *
     * @param Zend_Config $config Configuration object
     * @return Zend_Controller_Router_Route_Regex
     */
    public static function getInstance(Zend_Config $config)
    {
        $defs    = ($config->defaults instanceof Zend_Config) ? $config->defaults->toArray() : array();
        $map     = ($config->map instanceof Zend_Config) ? $config->map->toArray() : array();
        $reverse = (isset($config->reverse)) ? $config->reverse : null;

        return new self($config->route, $defs, $map, $reverse);
    }

    /**
     * Constructor
     *
     * @param       $route
     * @param array $defaults
     * @param array $map
     * @param null  $reverse
     */
    public function __construct($route, $defaults = array(), $map = array(), $reverse = null)
    {
        $this->_regex    = $route;
        $this->_defaults = (array) $defaults;
        $this->_map      = (array) $map;
        $this->_reverse  = $reverse;
    }

    /**
     * Get the version of the route
     *
     * @return int
     */
    public function getVersion()
    {
        return 1;
    }

    /**
     * Matches a user submitted path with a previously defined route.
     * Assigns and returns an array of defaults on a successful match.
     *
     * @param  string $path Path used to match against this routing map
     * @return array|false  An array of assigned values or a false on a mismatch
     */
    public function match($path, $partial = false)
    {
        if (!$partial) {
            $path  = trim(urldecode($path), self::URI_DELIMITER);
            $regex = '#^' . $this->_regex . '$#i';
        } else {
            $regex = '#^' . $this->_regex . '#i';
        }

        $res = preg_match($regex, $path, $values);

        if ($res === 0) {
            return false;
        }

        if ($partial) {
            $this->setMatchedPath($values[0]);
        }

        // array_filter_key()? Why isn't this in a standard PHP function set yet? :)
        foreach ($values as $i => $value) {
            if (!is_int($i) || $i === 0) {
                unset($values[$i]);
            }
        }

        $this->_values = $values;

        $values   = $this->_getMappedValues($values);
        $defaults = $this->_getMappedValues($this->_defaults, false, true);
        $return   = $values + $defaults;

        return $return;
    }

    /**
     * Maps numerically indexed array values to it's associative mapped counterpart.
     * Or vice versa. Uses user provided map array which consists of index => name
     * parameter mapping. If map is not found, it returns original array.
     *
     * Method strips destination type of keys form source array. Ie. if source array is
     * indexed numerically then every associative key will be stripped. Vice versa if reversed
     * is set to true.
     *
     * @param  array   $values   Indexed or associative array of values to map
     * @param  boolean $reversed False means translation of index to association. True means reverse.
     * @param  boolean $preserve Should wrong type of keys be preserved or stripped.
     * @return array   An array of mapped values
     */
    protected function _getMappedValues($values, $reversed = false, $preserve = false)
    {
        if (count($this->_map) == 0) {
            return $values;
        }

        $return = array();

        foreach ($values as $key => $value) {
            if (is_int($key) && !$reversed) {
                if (array_key_exists($key, $this->_map)) {
                    $index = $this->_map[$key];
                } elseif (false === ($index = array_search($key, $this->_map))) {
                    $index = $key;
                }
                $return[$index] = $values[$key];
            } elseif ($reversed) {
                $index = $key;
                if (!is_int($key)) {
                    if (array_key_exists($key, $this->_map)) {
                        $index = $this->_map[$key];
                    } else {
                        $index = array_search($key, $this->_map, true);
                    }
                }
                if (false !== $index) {
                    $return[$index] = $values[$key];
                }
            } elseif ($preserve) {
                $return[$key] = $value;
            }
        }

        return $return;
    }

    /**
     * Assembles a URL path defined by this route
     *
     * @param  array   $data An array of name (or index) and value pairs used as parameters
     * @param  boolean $reset
     * @param  boolean $encode
     * @param  boolean $partial
     * @throws Zend_Controller_Router_Exception
     * @return string Route path with user submitted parameters
     */
    public function assemble($data = array(), $reset = false, $encode = false, $partial = false)
    {
        if ($this->_reverse === null) {
            #require_once 'Zend/Controller/Router/Exception.php';
            throw new Zend_Controller_Router_Exception('Cannot assemble. Reversed route is not specified.');
        }

        $defaultValuesMapped = $this->_getMappedValues($this->_defaults, true, false);
        $matchedValuesMapped = $this->_getMappedValues($this->_values, true, false);
        $dataValuesMapped    = $this->_getMappedValues($data, true, false);

        // handle resets, if so requested (By null value) to do so
        if (($resetKeys = array_search(null, $dataValuesMapped, true)) !== false) {
            foreach ((array)$resetKeys as $resetKey) {
                if (isset($matchedValuesMapped[$resetKey])) {
                    unset($matchedValuesMapped[$resetKey]);
                    unset($dataValuesMapped[$resetKey]);
                }
            }
        }

        // merge all the data together, first defaults, then values matched, then supplied
        $mergedData = $defaultValuesMapped;
        $mergedData = $this->_arrayMergeNumericKeys($mergedData, $matchedValuesMapped);
        $mergedData = $this->_arrayMergeNumericKeys($mergedData, $dataValuesMapped);

        if ($encode) {
            foreach ($mergedData as $key => &$value) {
                $value = urlencode($value);
            }
        }

        ksort($mergedData);

        $return = @vsprintf($this->_reverse, $mergedData);

        if ($return === false) {
            #require_once 'Zend/Controller/Router/Exception.php';
            throw new Zend_Controller_Router_Exception('Cannot assemble. Too few arguments?');
        }

        return $return;
    }

    /**
     * Return a single parameter of route's defaults
     *
     * @param string $name Array key of the parameter
     * @return string Previously set default
     */
    public function getDefault($name)
    {
        if (isset($this->_defaults[$name])) {
            return $this->_defaults[$name];
        }
    }

    /**
     * Return an array of defaults
     *
     * @return array Route defaults
     */
    public function getDefaults()
    {
        return $this->_defaults;
    }

    /**
     * Get all variables which are used by the route
     *
     * @return array
     */
    public function getVariables()
    {
        $variables = array();

        foreach ($this->_map as $key => $value) {
            if (is_numeric($key)) {
                $variables[] = $value;
            } else {
                $variables[] = $key;
            }
        }

        return $variables;
    }

    /**
     * _arrayMergeNumericKeys() - allows for a strict key (numeric's included) array_merge.
     * php's array_merge() lacks the ability to merge with numeric keys.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    protected function _arrayMergeNumericKeys(Array $array1, Array $array2)
    {
        $returnArray = $array1;
        foreach ($array2 as $array2Index => $array2Value) {
            $returnArray[$array2Index] = $array2Value;
        }

        return $returnArray;
    }
}
