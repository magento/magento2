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
 * @package    Zend_Tag
 * @subpackage Item
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Tag_Taggable
 */
#require_once 'Zend/Tag/Taggable.php';

/**
 * @category   Zend
 * @package    Zend_Tag
 * @uses       Zend_Tag_Taggable
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tag_Item implements Zend_Tag_Taggable
{
    /**
     * Title of the tag
     *
     * @var string
     */
    protected $_title = null;

    /**
     * Weight of the tag
     *
     * @var float
     */
    protected $_weight = null;

    /**
     * Custom parameters
     *
     * @var string
     */
    protected $_params = array();

    /**
     * Option keys to skip when calling setOptions()
     *
     * @var array
     */
    protected $_skipOptions = array(
        'options',
        'param'
    );

    /**
     * Create a new tag according to the options
     *
     * @param  array|Zend_Config $options
     * @throws Zend_Tag_Exception When invalid options are provided
     * @throws Zend_Tag_Exception When title was not set
     * @throws Zend_Tag_Exception When weight was not set
     * @return void
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (!is_array($options)) {
            #require_once 'Zend/Tag/Exception.php';
            throw new Zend_Tag_Exception('Invalid options provided to constructor');
        }

        $this->setOptions($options);

        if ($this->_title === null) {
            #require_once 'Zend/Tag/Exception.php';
            throw new Zend_Tag_Exception('Title was not set');
        }

        if ($this->_weight === null) {
            #require_once 'Zend/Tag/Exception.php';
            throw new Zend_Tag_Exception('Weight was not set');
        }
    }

    /**
     * Set options of the tag
     *
     * @param  array $options
     * @return Zend_Tag_Item
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            if (in_array(strtolower($key), $this->_skipOptions)) {
                continue;
            }

            $method = 'set' . $key;
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * Defined by Zend_Tag_Taggable
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Set the title
     *
     * @param  string $title
     * @throws Zend_Tag_Exception When title is no string
     * @return Zend_Tag_Item
     */
    public function setTitle($title)
    {
        if (!is_string($title)) {
            #require_once 'Zend/Tag/Exception.php';
            throw new Zend_Tag_Exception('Title must be a string');
        }

        $this->_title = (string) $title;
        return $this;
    }

    /**
     * Defined by Zend_Tag_Taggable
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->_weight;
    }

    /**
     * Set the weight
     *
     * @param  float $weight
     * @throws Zend_Tag_Exception When weight is not numeric
     * @return Zend_Tag_Item
     */
    public function setWeight($weight)
    {
        if (!is_numeric($weight)) {
            #require_once 'Zend/Tag/Exception.php';
            throw new Zend_Tag_Exception('Weight must be numeric');
        }

        $this->_weight = (float) $weight;
        return $this;
    }

    /**
     * Set multiple params at once
     *
     * @param  array $params
     * @return Zend_Tag_Item
     */
    public function setParams(array $params)
    {
        foreach ($params as $name => $value) {
            $this->setParam($name, $value);
        }

        return $this;
    }

    /**
     * Defined by Zend_Tag_Taggable
     *
     * @param  string $name
     * @param  mixed  $value
     * @return Zend_Tag_Item
     */
    public function setParam($name, $value)
    {
        $this->_params[$name] = $value;
        return $this;
    }

    /**
     * Defined by Zend_Tag_Taggable
     *
     * @param  string $name
     * @return mixed
     */
    public function getParam($name)
    {
        if (isset($this->_params[$name])) {
            return $this->_params[$name];
        }
        return null;
    }
}
