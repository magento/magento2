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
 * @package    Zend_Captcha
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** @see Zend_Captcha_Adapter */
#require_once 'Zend/Captcha/Adapter.php';

/** @see Zend_Validate_Abstract */
#require_once 'Zend/Validate/Abstract.php';

/**
 * Base class for Captcha adapters
 *
 * Provides some utility functionality to build on
 *
 * @category   Zend
 * @package    Zend_Captcha
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Base.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
abstract class Zend_Captcha_Base extends Zend_Validate_Abstract implements Zend_Captcha_Adapter
{
    /**
     * Element name
     *
     * Useful to generate/check form fields
     *
     * @var string
     */
    protected $_name;

    /**
     * Captcha options
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Options to skip when processing options
     * @var array
     */
    protected $_skipOptions = array(
        'options',
        'config',
    );

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * Constructor
     *
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct($options = null)
    {
        // Set options
        if (is_array($options)) {
            $this->setOptions($options);
        } else if ($options instanceof Zend_Config) {
            $this->setConfig($options);
        }
    }

    /**
     * Set single option for the object
     *
     * @param string $key
     * @param string $value
     * @return Zend_Form_Element
     */
    public function setOption($key, $value)
    {
        if (in_array(strtolower($key), $this->_skipOptions)) {
            return $this;
        }

        $method = 'set' . ucfirst ($key);
        if (method_exists ($this, $method)) {
            // Setter exists; use it
            $this->$method ($value);
            $this->_options[$key] = $value;
        } elseif (property_exists($this, $key)) {
            // Assume it's metadata
            $this->$key = $value;
            $this->_options[$key] = $value;
        }
        return $this;
    }

    /**
     * Set object state from options array
     *
     * @param  array $options
     * @return Zend_Form_Element
     */
    public function setOptions($options = null)
    {
        foreach ($options as $key => $value) {
            $this->setOption($key, $value);
        }
        return $this;
    }

    /**
     * Retrieve options representing object state
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Set object state from config object
     *
     * @param  Zend_Config $config
     * @return Zend_Captcha_Base
     */
    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config->toArray());
    }

    /**
     * Get optional decorator
     *
     * By default, return null, indicating no extra decorator needed.
     *
     * @return null
     */
    public function getDecorator()
    {
        return null;
    }
}
