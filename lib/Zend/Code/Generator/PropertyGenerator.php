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

use Zend\Code\Reflection\PropertyReflection;

/**
 * @category   Zend
 * @package    Zend_Code_Generator
 */
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
     * fromReflection()
     *
     * @param PropertyReflection $reflectionProperty
     * @return PropertyGenerator
     */
    public static function fromReflection(PropertyReflection $reflectionProperty)
    {
        $property = new self();

        $property->setName($reflectionProperty->getName());

        $allDefaultProperties = $reflectionProperty->getDeclaringClass()->getDefaultProperties();

        $property->setDefaultValue($allDefaultProperties[$reflectionProperty->getName()]);

        if ($reflectionProperty->getDocComment() != '') {
            $property->setDocBlock(DocBlockGenerator::fromReflection($reflectionProperty->getDocComment()));
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

    public function __construct($name = null, $defaultValue = null, $flags = self::FLAG_PUBLIC)
    {
        if ($name !== null) {
            $this->setName($name);
        }
        if ($defaultValue !== null) {
            $this->setDefaultValue($defaultValue);
        }
        if ($flags !== self::FLAG_PUBLIC) {
            $this->setFlags($flags);
        }
    }

    /**
     * setConst()
     *
     * @param bool $const
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
    }

    /**
     * isConst()
     *
     * @return bool
     */
    public function isConst()
    {
        return ($this->flags & self::FLAG_CONSTANT);
    }

    /**
     * setDefaultValue()
     *
     * @param PropertyValueGenerator|string|array $defaultValue
     * @return PropertyGenerator
     */
    public function setDefaultValue($defaultValue)
    {
        // if it looks like
        if (is_array($defaultValue)
            && array_key_exists('value', $defaultValue)
            && array_key_exists('type', $defaultValue)
        ) {
            $defaultValue = new PropertyValueGenerator($defaultValue);
        }

        if (!($defaultValue instanceof PropertyValueGenerator)) {
            $defaultValue = new PropertyValueGenerator($defaultValue);
        }

        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * getDefaultValue()
     *
     * @return PropertyValueGenerator
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * generate()
     *
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
                throw new Exception\RuntimeException('The property ' . $this->name . ' is said to be '
                                                         . 'constant but does not have a valid constant value.');
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
