<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

class ClassWithAllArgumentTypes
{
    const DEFAULT_VALUE = 'Const Value';

    /**
     * @var stdClass
     */
    protected $_stdClassObject;

    /**
     * @var classWithoutConstruct
     */
    protected $_withoutConstructorClassObject;

    /**
     * @var mixed
     */
    protected $_someVariable;

    /**
     * @var int
     */
    protected $_optionalNumValue;

    /**
     * @var string
     */
    protected $_optionalStringValue;

    /**
     * @var array
     */
    protected $_optionalArrayValue;

    /**
     * @var mixed
     */
    protected $_constValue;

    /**
     * @param stdClass $stdClassObject
     * @param ClassWithoutConstruct $withoutConstructorClassObject
     * @param $someVariable
     * @param string $const
     * @param int $optionalNumValue
     * @param string $optionalStringValue
     * @param array $optionalArrayValue
     */
    public function __construct(
        \stdClass $stdClassObject,
        \ClassWithoutConstruct $withoutConstructorClassObject,
        $someVariable,
        $const = \ClassWithAllArgumentTypes::DEFAULT_VALUE,
        $optionalNumValue = 9807,
        $optionalStringValue = 'optional string',
        $optionalArrayValue = ['optionalKey' => 'optionalValue']
    ) {
        $this->_stdClassObject = $stdClassObject;
        $this->_withoutConstructorClassObject = $withoutConstructorClassObject;
        $this->_someVariable = $someVariable;
        $this->_optionalNumValue = $optionalNumValue;
        $this->_optionalStringValue = $optionalStringValue;
        $this->_optionalArrayValue = $optionalArrayValue;
        $this->_constValue = $const;
    }
}
class ClassWithoutOwnConstruct extends ClassWithAllArgumentTypes
{
}
class ClassWithoutConstruct
{
}
class ClassExtendsDefaultPhpType extends \RuntimeException
{
}
class ClassExtendsDefaultPhpTypeWithIOverrideConstructor extends \RuntimeException
{
    /**
     * Override constructor due to Reflection API incorrect work with internal PHP classes.
     * Obtaining of default argument value and default argument type is incorrect
     *
     * @param string $message
     * @param int $code
     * @param Exception $previous
     */
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class FirstClassForParentCall
{
    /**
     * @var stdClass
     */
    protected $_stdClassObject;

    /**
     * @var ClassExtendsDefaultPhpType
     */
    protected $_runeTimeException;

    /**
     * @var array
     */
    protected $_arrayVariable;

    /**
     * @param stdClass $stdClassObject
     * @param ClassExtendsDefaultPhpType $runeTimeException
     * @param array $arrayVariable
     */
    public function __construct(
        \stdClass $stdClassObject,
        \ClassExtendsDefaultPhpType $runeTimeException,
        $arrayVariable = ['key' => 'value']
    ) {
        $this->_stdClassObject = $stdClassObject;
        $this->_runeTimeException = $runeTimeException;
        $this->_arrayVariable = $arrayVariable;
    }
}
class ThirdClassForParentCall extends firstClassForParentCall
{
    /**
     * @var stdClass
     */
    protected $_stdClassObject;

    /**
     * @var ClassExtendsDefaultPhpType
     */
    protected $_secondClass;

    /**
     * @param stdClass $stdClassObject
     * @param ClassExtendsDefaultPhpType $secondClass
     */
    public function __construct(\stdClass $stdClassObject, \ClassExtendsDefaultPhpType $secondClass)
    {
        parent::__construct($stdClassObject, $secondClass);
        $this->_stdClassObject = $stdClassObject;
        $this->_secondClass = $secondClass;
    }
}
class WrongArgumentsOrder extends firstClassForParentCall
{
    /**
     * @var stdClass
     */
    protected $_stdClassObject;

    /**
     * @var ClassExtendsDefaultPhpType
     */
    protected $_secondClass;

    /**
     * @param stdClass $stdClassObject
     * @param ClassExtendsDefaultPhpType $secondClass
     */
    public function __construct(\stdClass $stdClassObject, \ClassExtendsDefaultPhpType $secondClass)
    {
        parent::__construct($secondClass, $stdClassObject);
        $this->_stdClassObject = $stdClassObject;
        $this->_secondClass = $secondClass;
    }
}
class ArgumentsOnSeparateLines extends firstClassForParentCall
{
    /**
     * @var stdClass
     */
    protected $_stdClassObject;

    /**
     * @var ClassExtendsDefaultPhpType
     */
    protected $_secondClass;

    /**
     * @param stdClass $stdClassObject
     * @param ClassExtendsDefaultPhpType $secondClass
     */
    public function __construct(\stdClass $stdClassObject, \ClassExtendsDefaultPhpType $secondClass)
    {
        parent::__construct($secondClass, $stdClassObject);
        $this->_stdClassObject = $stdClassObject;
        $this->_secondClass = $secondClass;
    }
}
class ClassWithSuppressWarnings
{
    /**
     * @var stdClass
     */
    protected $argumentOne;

    /**
     * @var ClassExtendsDefaultPhpType
     */
    protected $argumentTwo;

    /**
     * @param stdClass $stdClassObject
     * @param ClassExtendsDefaultPhpType $secondClass
     *
     * @SuppressWarnings(Magento.TypeDuplication)
     */
    public function __construct(\stdClass $stdClassObject, \ClassExtendsDefaultPhpType $secondClass)
    {
        $this->argumentOne = $stdClassObject;
        $this->argumentTwo = $secondClass;
    }
}
