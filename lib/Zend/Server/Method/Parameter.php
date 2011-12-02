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
 * @package    Zend_Server
 * @subpackage Method
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Parameter.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Method parameter metadata
 *
 * @category   Zend
 * @package    Zend_Server
 * @subpackage Method
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Server_Method_Parameter
{
    /**
     * @var mixed Default parameter value
     */
    protected $_defaultValue;

    /**
     * @var string Parameter description
     */
    protected $_description = '';

    /**
     * @var string Parameter variable name
     */
    protected $_name;

    /**
     * @var bool Is parameter optional?
     */
    protected $_optional = false;

    /**
     * @var string Parameter type
     */
    protected $_type = 'mixed';

    /**
     * Constructor
     *
     * @param  null|array $options
     * @return void
     */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Set object state from array of options
     *
     * @param  array $options
     * @return Zend_Server_Method_Parameter
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Set default value
     *
     * @param  mixed $defaultValue
     * @return Zend_Server_Method_Parameter
     */
    public function setDefaultValue($defaultValue)
    {
        $this->_defaultValue = $defaultValue;
        return $this;
    }

    /**
     * Retrieve default value
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->_defaultValue;
    }

    /**
     * Set description
     *
     * @param  string $description
     * @return Zend_Server_Method_Parameter
     */
    public function setDescription($description)
    {
        $this->_description = (string) $description;
        return $this;
    }

    /**
     * Retrieve description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Set name
     *
     * @param  string $name
     * @return Zend_Server_Method_Parameter
     */
    public function setName($name)
    {
        $this->_name = (string) $name;
        return $this;
    }

    /**
     * Retrieve name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set optional flag
     *
     * @param  bool $flag
     * @return Zend_Server_Method_Parameter
     */
    public function setOptional($flag)
    {
        $this->_optional = (bool) $flag;
        return $this;
    }

    /**
     * Is the parameter optional?
     *
     * @return bool
     */
    public function isOptional()
    {
        return $this->_optional;
    }

    /**
     * Set parameter type
     *
     * @param  string $type
     * @return Zend_Server_Method_Parameter
     */
    public function setType($type)
    {
        $this->_type = (string) $type;
        return $this;
    }

    /**
     * Retrieve parameter type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Cast to array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'type'         => $this->getType(),
            'name'         => $this->getName(),
            'optional'     => $this->isOptional(),
            'defaultValue' => $this->getDefaultValue(),
            'description'  => $this->getDescription(),
        );
    }
}
