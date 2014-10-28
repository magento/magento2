<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
        $optionalArrayValue = array('optionalKey' => 'optionalValue')
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
        $arrayVariable = array('key' => 'value')
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
