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
namespace Magento\Framework\Code\Reader;


require_once __DIR__ . '/_files/ClassesForArgumentsReader.php';
class ArgumentsReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Code\Reader\ArgumentsReader
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\Code\Reader\ArgumentsReader();
    }

    public function testGetConstructorArgumentsClassWithAllArgumentsType()
    {
        $expectedResult = array(
            'stdClassObject' => array(
                'name' => 'stdClassObject',
                'position' => 0,
                'type' => '\stdClass',
                'isOptional' => false,
                'default' => null
            ),
            'withoutConstructorClassObject' => array(
                'name' => 'withoutConstructorClassObject',
                'position' => 1,
                'type' => '\ClassWithoutConstruct',
                'isOptional' => false,
                'default' => null
            ),
            'someVariable' => array(
                'name' => 'someVariable',
                'position' => 2,
                'type' => null,
                'isOptional' => false,
                'default' => null
            ),
            'const' => array(
                'name' => 'const',
                'position' => 3,
                'type' => null,
                'isOptional' => true,
                'default' => "'Const Value'"
            ),
            'optionalNumValue' => array(
                'name' => 'optionalNumValue',
                'position' => 4,
                'type' => null,
                'isOptional' => true,
                'default' => 9807
            ),
            'optionalStringValue' => array(
                'name' => 'optionalStringValue',
                'position' => 5,
                'type' => null,
                'isOptional' => true,
                'default' => "'optional string'"
            ),
            'optionalArrayValue' => array(
                'name' => 'optionalArrayValue',
                'position' => 6,
                'type' => null,
                'isOptional' => true,
                'default' => "array('optionalKey' => 'optionalValue')"
            )
        );
        $class = new \ReflectionClass('ClassWithAllArgumentTypes');
        $actualResult = $this->_model->getConstructorArguments($class);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testGetConstructorArgumentsClassWithoutOwnConstructorInheritedFalse()
    {
        $class = new \ReflectionClass('classWithoutOwnConstruct');
        $actualResult = $this->_model->getConstructorArguments($class);

        $this->assertEquals(array(), $actualResult);
    }

    public function testGetConstructorArgumentsClassWithoutOwnConstructorInheritedTrue()
    {
        $expectedResult = array(
            'stdClassObject' => array(
                'name' => 'stdClassObject',
                'position' => 0,
                'type' => '\stdClass',
                'isOptional' => false,
                'default' => null
            ),
            'withoutConstructorClassObject' => array(
                'name' => 'withoutConstructorClassObject',
                'position' => 1,
                'type' => '\ClassWithoutConstruct',
                'isOptional' => false,
                'default' => null
            ),
            'someVariable' => array(
                'name' => 'someVariable',
                'position' => 2,
                'type' => null,
                'isOptional' => false,
                'default' => null
            ),
            'const' => array(
                'name' => 'const',
                'position' => 3,
                'type' => null,
                'isOptional' => true,
                'default' => "'Const Value'"
            ),
            'optionalNumValue' => array(
                'name' => 'optionalNumValue',
                'position' => 4,
                'type' => null,
                'isOptional' => true,
                'default' => 9807
            ),
            'optionalStringValue' => array(
                'name' => 'optionalStringValue',
                'position' => 5,
                'type' => null,
                'isOptional' => true,
                'default' => "'optional string'"
            ),
            'optionalArrayValue' => array(
                'name' => 'optionalArrayValue',
                'position' => 6,
                'type' => null,
                'isOptional' => true,
                'default' => "array('optionalKey' => 'optionalValue')"
            )
        );
        $class = new \ReflectionClass('ClassWithoutOwnConstruct');
        $actualResult = $this->_model->getConstructorArguments($class, false, true);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testGetConstructorArgumentsClassWithoutConstructInheridetFalse()
    {
        $class = new \ReflectionClass('ClassWithoutConstruct');
        $actualResult = $this->_model->getConstructorArguments($class);

        $this->assertEquals(array(), $actualResult);
    }

    public function testGetConstructorArgumentsClassWithoutConstructInheridetTrue()
    {
        $class = new \ReflectionClass('ClassWithoutConstruct');
        $actualResult = $this->_model->getConstructorArguments($class, false, true);

        $this->assertEquals(array(), $actualResult);
    }

    public function testGetConstructorArgumentsClassExtendsDefaultPhpTypeInheridetFalse()
    {
        $class = new \ReflectionClass('ClassExtendsDefaultPhpType');
        $actualResult = $this->_model->getConstructorArguments($class);

        $this->assertEquals(array(), $actualResult);
    }

    public function testGetConstructorArgumentsClassExtendsDefaultPhpTypeInheridetTrue()
    {
        $expectedResult = array(
            'message' => array(
                'name' => 'message',
                'position' => 0,
                'type' => null,
                'isOptional' => true,
                'default' => null
            ),
            'code' => array(
                'name' => 'code',
                'position' => 1,
                'type' => null,
                'isOptional' => true,
                'default' => null
            ),
            'previous' => array(
                'name' => 'previous',
                'position' => 2,
                'type' => null,
                'isOptional' => true,
                'default' => null
            )
        );
        $class = new \ReflectionClass('ClassExtendsDefaultPhpType');
        $actualResult = $this->_model->getConstructorArguments($class, false, true);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testGetParentCallWithRightArgumentsOrder()
    {
        $class = new \ReflectionClass('ThirdClassForParentCall');
        $actualResult = $this->_model->getParentCall(
            $class,
            array(
                'stdClassObject' => array('type' => '\stdClass'),
                'secondClass' => array('type' => '\ClassExtendsDefaultPhpType')
            )
        );
        $expectedResult = array(
            array('name' => 'stdClassObject', 'position' => 0, 'type' => '\stdClass'),
            array('name' => 'secondClass', 'position' => 1, 'type' => '\ClassExtendsDefaultPhpType')
        );
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testGetParentCallWithWrongArgumentsOrder()
    {
        $class = new \ReflectionClass('WrongArgumentsOrder');
        $actualResult = $this->_model->getParentCall(
            $class,
            array(
                'stdClassObject' => array('type' => '\stdClass'),
                'secondClass' => array('type' => '\ClassExtendsDefaultPhpType')
            )
        );
        $expectedResult = array(
            array('name' => 'secondClass', 'position' => 0, 'type' => '\ClassExtendsDefaultPhpType'),
            array('name' => 'stdClassObject', 'position' => 1, 'type' => '\stdClass')
        );
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testGetParentCallWithSeparateLineFormat()
    {
        $class = new \ReflectionClass('ThirdClassForParentCall');
        $actualResult = $this->_model->getParentCall(
            $class,
            array(
                'stdClassObject' => array('type' => '\stdClass'),
                'secondClass' => array('type' => '\ClassExtendsDefaultPhpType')
            )
        );
        $expectedResult = array(
            array('name' => 'stdClassObject', 'position' => 0, 'type' => '\stdClass'),
            array('name' => 'secondClass', 'position' => 1, 'type' => '\ClassExtendsDefaultPhpType')
        );
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @param string $requiredType
     * @param string $actualType
     * @param bool $expectedResult
     * @dataProvider testIsCompatibleTypeDataProvider
     */
    public function testIsCompatibleType($requiredType, $actualType, $expectedResult)
    {
        $actualResult = $this->_model->isCompatibleType($requiredType, $actualType);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testIsCompatibleTypeDataProvider()
    {
        return array(
            array('array', 10, false),
            array('array', 'array', true),
            array(null, null, true),
            array(null, 'array', true),
            array('\ClassWithAllArgumentTypes', '\ClassWithoutOwnConstruct', true),
            array('\ClassWithoutOwnConstruct', '\ClassWithAllArgumentTypes', false)
        );
    }

    public function testGetAnnotations()
    {
        $class = new \ReflectionClass('\ClassWithSuppressWarnings');
        $expected = array('SuppressWarnings' => 'Magento.TypeDuplication');
        $this->assertEquals($expected, $this->_model->getAnnotations($class));
    }
}
