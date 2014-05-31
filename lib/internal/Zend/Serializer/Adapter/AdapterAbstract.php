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
 * @package    Zend_Serializer
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: AdapterAbstract.php 20574 2010-01-24 17:39:14Z mabe $
 */

/** @see Zend_Serializer_Adapter_AdapterInterface */
#require_once 'Zend/Serializer/Adapter/AdapterInterface.php';

/**
 * @category   Zend
 * @package    Zend_Serializer
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Serializer_Adapter_AdapterAbstract implements Zend_Serializer_Adapter_AdapterInterface
{
    /**
     * Serializer options
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Constructor
     *
     * @param array|Zend_Config $opts Serializer options
     */
    public function __construct($opts = array()) 
    {
        $this->setOptions($opts);
    }

    /**
     * Set serializer options
     *
     * @param  array|Zend_Config $opts Serializer options
     * @return Zend_Serializer_Adapter_AdapterAbstract
     */
    public function setOptions($opts) 
    {
        if ($opts instanceof Zend_Config) {
            $opts = $opts->toArray();
        } else {
            $opts = (array) $opts;
        }

        foreach ($opts as $k => $v) {
            $this->setOption($k, $v);
        }
        return $this;
    }

    /**
     * Set a serializer option
     *
     * @param  string $name Option name
     * @param  mixed $value Option value
     * @return Zend_Serializer_Adapter_AdapterAbstract
     */
    public function setOption($name, $value) 
    {
        $this->_options[(string) $name] = $value;
        return $this;
    }

    /**
     * Get serializer options
     *
     * @return array
     */
    public function getOptions() 
    {
        return $this->_options;
    }

    /**
     * Get a serializer option
     *
     * @param  string $name
     * @return mixed
     * @throws Zend_Serializer_Exception
     */
    public function getOption($name) 
    {
        $name = (string) $name;
        if (!array_key_exists($name, $this->_options)) {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('Unknown option name "'.$name.'"');
        }

        return $this->_options[$name];
    }
}
