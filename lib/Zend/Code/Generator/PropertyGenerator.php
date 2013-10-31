<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator;

use Zend\Code\Reflection\PropertyReflection;

class PropertyGenerator extends AbstractMemberGenerator
{
    const FLAG_CONSTANT = 0x08;

    /**
     * @var bool
     */
    protected $isConst = null;

    /**
     * @var PropertyValueGenerator
     */
    protected $defaultValue = null;

    /**
     * @param  PropertyReflection $reflectionProperty
     * @return PropertyGenerator
     */
    public static function fromReflection(PropertyReflection $reflectionProperty)
    {
        $property = new static();

        $property->setName($reflectionProperty->getName());

        $allDefaultProperties = $reflectionProperty->getDeclaringClass()->getDefaultProperties();

        $property->setDefaultValue($allDefaultProperties[$reflectionProperty->getName()]);

        if ($reflectionProperty->getDocComment() != '') {
            $property->setDocBlock(DocBlockGenerator::fromReflection($reflectionProperty->getDocBlock()));
        }

        if ($reflectionProperty->isStatic()) {
            $property->setStatic(true);
        }

        if ($reflectionProperty->isPrivate()) {
            $property->setVisibility(self::VISIBILITY_PRIVATE);
        } elseif ($reflectionProperty->isProtected()) {
            $property->setVisibility(self::VISIBILITY_PROTECTED);
        } else {
            $property->setVisibility(self::VISIBILITY_PUBLIC);
        }

        $property->setSourceDirty(false);

        return $property;
    }

    /**
     * Generate from array
     *
     * @configkey name         string                                          [required] Class Name
     * @configkey const        bool
     * @configkey defaultvalue null|bool|string|int|float|array|ValueGenerator
     * @configkey flags        int
     * @configkey abstract     bool
     * @configkey final        bool
     * @configkey static       bool
     * @configkey visibility   string
     *
     * @throws Exception\InvalidArgumentException
     * @param  array $array
     * @return PropertyGenerator
     */
    public static function fromArray(array $array)
    {
        if (!isset($array['name'])) {
            throw new Exception\InvalidArgumentException(
                'Property generator requires that a name is provided for this object'
            );
        }

        $property = new static($array['name']);
        foreach ($array as $name => $value) {
            // normalize key
            switch (strtolower(str_replace(array('.', '-', '_'), '', $name))) {
                case 'const':
                    $property->setConst($value);
                    break;
                case 'defaultvalue':
                    $property->setDefaultValue($value);
                    break;
                case 'docblock':
                    $docBlock = ($value instanceof DocBlockGenerator) ? $value : DocBlockGenerator::fromArray($value);
                    $property->setDocBlock($docBlock);
                    break;
                case 'flags':
                    $property->setFlags($value);
                    break;
                case 'abstract':
                    $property->setAbstract($value);
                    break;
                case 'final':
                    $property->setFinal($value);
                    break;
                case 'static':
                    $property->setStatic($value);
                    break;
                case 'visibility':
                    $property->setVisibility($value);
                    break;
            }
        }

        return $property;
    }

    /**
     * @param  string $name
     * @param PropertyValueGenerator|string|array $defaultValue
     * @param  int|array $flags
     */
    public function __construct($name = null, $defaultValue = null, $flags = self::FLAG_PUBLIC)
    {
        if (null !== $name) {
            $this->setName($name);
        }
        if (null !== $defaultValue) {
            $this->setDefaultValue($defaultValue);
        }
        if ($flags !== self::FLAG_PUBLIC) {
            $this->setFlags($flags);
        }
    }

    /**
     * @param  bool $const
     * @return PropertyGenerator
     */
    public function setConst($const)
    {
        if ($const) {
            $this->removeFlag(self::FLAG_PUBLIC | self::FLAG_PRIVATE | self::FLAG_PROTECTED);
            $this->setFlags(self::FLAG_CONSTANT);
        } else {
            $this->removeFlag(self::FLAG_CONSTANT);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isConst()
    {
        return (bool) ($this->flags & self::FLAG_CONSTANT);
    }

    /**
     * @param PropertyValueGenerator|mixed $defaultValue
     * @param string                       $defaultValueType
     * @param string                       $defaultValueOutputMode
     *
     * @return PropertyGenerator
     */
    public function setDefaultValue($defaultValue, $defaultValueType = PropertyValueGenerator::TYPE_AUTO, $defaultValueOutputMode = PropertyValueGenerator::OUTPUT_MULTIPLE_LINE)
    {
        if (!($defaultValue instanceof PropertyValueGenerator)) {

            $defaultValue = new PropertyValueGenerator($defaultValue, $defaultValueType, $defaultValueOutputMode);
        }

        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @return PropertyValueGenerator
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @throws Exception\RuntimeException
     * @return string
     */
    public function generate()
    {
        $name         = $this->getName();
        $defaultValue = $this->getDefaultValue();

        $output = '';

        if (($docBlock = $this->getDocBlock()) !== null) {
            $docBlock->setIndentation('    ');
            $output .= $docBlock->generate();
        }

        if ($this->isConst()) {
            if ($defaultValue != null && !$defaultValue->isValidConstantType()) {
                throw new Exception\RuntimeException(sprintf(
                    'The property %s is said to be '
                    . 'constant but does not have a valid constant value.',
                    $this->name
                ));
            }
            $output .= $this->indentation . 'const ' . $name . ' = '
                . (($defaultValue !== null) ? $defaultValue->generate() : 'null;');
        } else {
            $output .= $this->indentation
                . $this->getVisibility()
                . (($this->isStatic()) ? ' static' : '')
                . ' $' . $name . ' = '
                . (($defaultValue !== null) ? $defaultValue->generate() : 'null;');
        }

        return $output;
    }
}
