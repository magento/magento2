<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator;

use Zend\Code\Reflection\ParameterReflection;

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
     * @param  ParameterReflection $reflectionParameter
     * @return ParameterGenerator
     */
    public static function fromReflection(ParameterReflection $reflectionParameter)
    {
        $param = new ParameterGenerator();
        $param->setName($reflectionParameter->getName());

        if ($reflectionParameter->isArray()) {
            $param->setType('array');
        } elseif (method_exists($reflectionParameter, 'isCallable') && $reflectionParameter->isCallable()) {
            $param->setType('callable');
        } else {
            $typeClass = $reflectionParameter->getClass();
            if ($typeClass) {
                $parameterType = $typeClass->getName();
                $currentNamespace = $reflectionParameter->getDeclaringClass()->getNamespaceName();

                if (!empty($currentNamespace) && substr($parameterType, 0, strlen($currentNamespace)) == $currentNamespace) {
                    $parameterType = substr($parameterType, strlen($currentNamespace) + 1);
                } else {
                    $parameterType = '\\' . trim($parameterType, '\\');
                }

                $param->setType($parameterType);
            }
        }

        $param->setPosition($reflectionParameter->getPosition());

        if ($reflectionParameter->isOptional()) {
            $param->setDefaultValue($reflectionParameter->getDefaultValue());
        }
        $param->setPassedByReference($reflectionParameter->isPassedByReference());

        return $param;
    }

    /**
     * Generate from array
     *
     * @configkey name              string                                          [required] Class Name
     * @configkey type              string
     * @configkey defaultvalue      null|bool|string|int|float|array|ValueGenerator
     * @configkey passedbyreference bool
     * @configkey position          int
     * @configkey sourcedirty       bool
     * @configkey indentation       string
     * @configkey sourcecontent     string
     *
     * @throws Exception\InvalidArgumentException
     * @param  array $array
     * @return ParameterGenerator
     */
    public static function fromArray(array $array)
    {
        if (!isset($array['name'])) {
            throw new Exception\InvalidArgumentException(
                'Paramerer generator requires that a name is provided for this object'
            );
        }

        $param = new static($array['name']);
        foreach ($array as $name => $value) {
            // normalize key
            switch (strtolower(str_replace(array('.', '-', '_'), '', $name))) {
                case 'type':
                    $param->setType($value);
                    break;
                case 'defaultvalue':
                    $param->setDefaultValue($value);
                    break;
                case 'passedbyreference':
                    $param->setPassedByReference($value);
                    break;
                case 'position':
                    $param->setPosition($value);
                    break;
                case 'sourcedirty':
                    $param->setSourceDirty($value);
                    break;
                case 'indentation':
                    $param->setIndentation($value);
                    break;
                case 'sourcecontent':
                    $param->setSourceContent($value);
                    break;
            }
        }

        return $param;
    }

    /**
     * @param  string $name
     * @param  string $type
     * @param  mixed $defaultValue
     * @param  int $position
     * @param  bool $passByReference
     */
    public function __construct($name = null, $type = null, $defaultValue = null, $position = null,
                                $passByReference = false)
    {
        if (null !== $name) {
            $this->setName($name);
        }
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $defaultValue) {
            $this->setDefaultValue($defaultValue);
        }
        if (null !== $position) {
            $this->setPosition($position);
        }
        if (false !== $passByReference) {
            $this->setPassedByReference(true);
        }
    }

    /**
     * @param  string $type
     * @return ParameterGenerator
     */
    public function setType($type)
    {
        $this->type = (string) $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param  string $name
     * @return ParameterGenerator
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
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
     * @param  null|bool|string|int|float|array|ValueGenerator $defaultValue
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
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param  int $position
     * @return ParameterGenerator
     */
    public function setPosition($position)
    {
        $this->position = (int) $position;
        return $this;
    }

    /**
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
     * @param  bool $passedByReference
     * @return ParameterGenerator
     */
    public function setPassedByReference($passedByReference)
    {
        $this->passedByReference = (bool) $passedByReference;
        return $this;
    }

    /**
     * @return string
     */
    public function generate()
    {
        $output = '';

        if ($this->type && !in_array($this->type, static::$simple)) {
            $output .= $this->type . ' ';
        }

        if (true === $this->passedByReference) {
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
