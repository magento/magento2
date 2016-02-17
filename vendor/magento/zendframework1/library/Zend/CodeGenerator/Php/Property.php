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
 * @package    Zend_CodeGenerator
 * @subpackage PHP
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_CodeGenerator_Php_Member_Abstract
 */
#require_once 'Zend/CodeGenerator/Php/Member/Abstract.php';

/**
 * @see Zend_CodeGenerator_Php_Property_DefaultValue
 */
#require_once 'Zend/CodeGenerator/Php/Property/DefaultValue.php';

/**
 * @category   Zend
 * @package    Zend_CodeGenerator
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_CodeGenerator_Php_Property extends Zend_CodeGenerator_Php_Member_Abstract
{

    /**
     * @var bool
     */
    protected $_isConst = null;

    /**
     * @var string
     */
    protected $_defaultValue = null;

    /**
     * fromReflection()
     *
     * @param Zend_Reflection_Property $reflectionProperty
     * @return Zend_CodeGenerator_Php_Property
     */
    public static function fromReflection(Zend_Reflection_Property $reflectionProperty)
    {
        $property = new self();

        $property->setName($reflectionProperty->getName());

        $allDefaultProperties = $reflectionProperty->getDeclaringClass()->getDefaultProperties();

        $property->setDefaultValue($allDefaultProperties[$reflectionProperty->getName()]);

        if ($reflectionProperty->getDocComment() != '') {
            $property->setDocblock(Zend_CodeGenerator_Php_Docblock::fromReflection($reflectionProperty->getDocComment()));
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
     * setConst()
     *
     * @param bool $const
     * @return Zend_CodeGenerator_Php_Property
     */
    public function setConst($const)
    {
        $this->_isConst = $const;
        return $this;
    }

    /**
     * isConst()
     *
     * @return bool
     */
    public function isConst()
    {
        return ($this->_isConst) ? true : false;
    }

    /**
     * setDefaultValue()
     *
     * @param Zend_CodeGenerator_Php_Property_DefaultValue|string|array $defaultValue
     * @return Zend_CodeGenerator_Php_Property
     */
    public function setDefaultValue($defaultValue)
    {
        // if it looks like
        if (is_array($defaultValue)
            && array_key_exists('value', $defaultValue)
            && array_key_exists('type', $defaultValue)) {
            $defaultValue = new Zend_CodeGenerator_Php_Property_DefaultValue($defaultValue);
        }

        if (!($defaultValue instanceof Zend_CodeGenerator_Php_Property_DefaultValue)) {
            $defaultValue = new Zend_CodeGenerator_Php_Property_DefaultValue(array('value' => $defaultValue));
        }

        $this->_defaultValue = $defaultValue;
        return $this;
    }

    /**
     * getDefaultValue()
     *
     * @return Zend_CodeGenerator_Php_Property_DefaultValue
     */
    public function getDefaultValue()
    {
        return $this->_defaultValue;
    }

    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        $name         = $this->getName();
        $defaultValue = $this->getDefaultValue();

        $output = '';

        if (($docblock = $this->getDocblock()) !== null) {
            $docblock->setIndentation('    ');
            $output .= $docblock->generate();
        }

        if ($this->isConst()) {
            if ($defaultValue != null && !$defaultValue->isValidConstantType()) {
                #require_once 'Zend/CodeGenerator/Php/Exception.php';
                throw new Zend_CodeGenerator_Php_Exception('The property ' . $this->_name . ' is said to be '
                    . 'constant but does not have a valid constant value.');
            }
            $output .= $this->_indentation . 'const ' . $name . ' = '
                . (($defaultValue !== null) ? $defaultValue->generate() : 'null;');
        } else {
            $output .= $this->_indentation
                . $this->getVisibility()
                . (($this->isStatic()) ? ' static' : '')
                . ' $' . $name . ' = '
                . (($defaultValue !== null) ? $defaultValue->generate() : 'null;');
        }
        return $output;
    }

}
