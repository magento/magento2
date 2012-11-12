<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Code
 */

namespace Zend\Code\Generator;

use Zend\Code\Reflection\ParameterReflection;

/**
 *
 * @category   Zend
 * @package    Zend_Code_Generator
 */
class ParameterGenerator extends AbstractGenerator
{
    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var string
     */
    protected $type = null;

    /**
     * @var string|ValueGenerator
     */
    protected $defaultValue = null;

    /**
     * @var int
     */
    protected $position = null;

    /**
     * @var bool
     */
    protected $passedByReference = false;

    /**
     * @var array
     */
    protected static $simple = array('int', 'bool', 'string', 'float', 'resource', 'mixed', 'object');

    /**
     * fromReflection()
     *
     * @param ParameterReflection $reflectionParameter
     * @return ParameterGenerator
     */
    public static function fromReflection(ParameterReflection $reflectionParameter)
    {
        $param = new ParameterGenerator();
        $param->setName($reflectionParameter->getName());

        if ($reflectionParameter->isArray()) {
            $param->setType('array');
        } else {
            $typeClass = $reflectionParameter->getClass();
            if ($typeClass !== null) {
                $param->setType($typeClass->getName());
            }
        }

        $param->setPosition($reflectionParameter->getPosition());

        if ($reflectionParameter->isOptional()) {
            $param->setDefaultValue($reflectionParameter->getDefaultValue());
        }
        $param->setPassedByReference($reflectionParameter->isPassedByReference());

        return $param;
    }

    public function __construct($name = null, $type = null, $defaultValue = null, $position = null,
                                $passByReference = false)
    {
        if ($name !== null) {
            $this->setName($name);
        }
        if ($type !== null) {
            $this->setType($type);
        }
        if ($defaultValue !== null) {
            $this->setDefaultValue($defaultValue);
        }
        if ($position !== null) {
            $this->setPosition($position);
        }
        if ($passByReference !== false) {
            $this->setPassedByReference(true);
        }
    }

    /**
     * setType()
     *
     * @param string $type
     * @return ParameterGenerator
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * getType()
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * setName()
     *
     * @param string $name
     * @return ParameterGenerator
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the default value of the parameter.
     *
     * Certain variables are difficult to express
     *
     * @param null|bool|string|int|float|array|ValueGenerator $defaultValue
     * @return ParameterGenerator
     */
    public function setDefaultValue($defaultValue)
    {
        if (!($defaultValue instanceof ValueGenerator)) {
            $defaultValue = new ValueGenerator($defaultValue);
        }
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * getDefaultValue()
     *
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * setPosition()
     *
     * @param int $position
     * @return ParameterGenerator
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * getPosition()
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function getPassedByReference()
    {
        return $this->passedByReference;
    }

    /**
     * @param bool $passedByReference
     * @return ParameterGenerator
     */
    public function setPassedByReference($passedByReference)
    {
        $this->passedByReference = $passedByReference;
        return $this;
    }

    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        $output = '';

        if ($this->type && !in_array($this->type, self::$simple)) {
            $output .= $this->type . ' ';
        }

        if ($this->passedByReference === true) {
            $output .= '&';
        }

        $output .= '$' . $this->name;

        if ($this->defaultValue !== null) {
            $output .= ' = ';
            if (is_string($this->defaultValue)) {
                $output .= ValueGenerator::escape($this->defaultValue);
            } elseif ($this->defaultValue instanceof ValueGenerator) {
                $this->defaultValue->setOutputMode(ValueGenerator::OUTPUT_SINGLE_LINE);
                $output .= $this->defaultValue;
            } else {
                $output .= $this->defaultValue;
            }
        }

        return $output;
    }

}
