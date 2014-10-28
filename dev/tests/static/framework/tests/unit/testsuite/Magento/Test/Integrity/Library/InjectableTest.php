<?php
/**
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
namespace Magento\Test\Integrity\Library;

use Magento\TestFramework\Integrity\Library\Injectable;

/**
 */
class InjectableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Injectable
     */
    protected $injectable;

    /**
     * @var \Zend\Code\Reflection\FileReflection
     */
    protected $fileReflection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $parameterReflection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $declaredClass;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->injectable = new Injectable();
        $this->fileReflection = $this->getMockBuilder(
            'Zend\Code\Reflection\FileReflection'
        )->disableOriginalConstructor()->getMock();

        $classReflection = $this->getMockBuilder(
            'Zend\Code\Reflection\ClassReflection'
        )->disableOriginalConstructor()->getMock();

        $methodReflection = $this->getMockBuilder(
            'Zend\Code\Reflection\MethodReflection'
        )->disableOriginalConstructor()->getMock();

        $this->parameterReflection = $this->getMockBuilder(
            'Zend\Code\Reflection\ParameterReflection'
        )->disableOriginalConstructor()->getMock();

        $this->declaredClass = $this->getMockBuilder(
            'Zend\Code\Reflection\ClassReflection'
        )->disableOriginalConstructor()->getMock();

        $methodReflection->expects(
            $this->once()
        )->method(
            'getDeclaringClass'
        )->will(
            $this->returnValue($this->declaredClass)
        );

        $methodReflection->expects(
            $this->any()
        )->method(
            'getParameters'
        )->will(
            $this->returnValue(array($this->parameterReflection))
        );

        $classReflection->expects(
            $this->once()
        )->method(
            'getMethods'
        )->will(
            $this->returnValue(array($methodReflection))
        );

        $this->fileReflection->expects(
            $this->once()
        )->method(
            'getClasses'
        )->will(
            $this->returnValue(array($classReflection))
        );
    }

    /**
     * Covered getDependencies
     *
     * @test
     */
    public function testGetDependencies()
    {
        $classReflection = $this->getMockBuilder(
            'Zend\Code\Reflection\ClassReflection'
        )->disableOriginalConstructor()->getMock();

        $classReflection->expects(
            $this->once()
        )->method(
            'getName'
        )->will(
            $this->returnValue('Magento\Core\Model\Object')
        );

        $this->parameterReflection->expects(
            $this->once()
        )->method(
            'getClass'
        )->will(
            $this->returnValue($classReflection)
        );

        $this->assertEquals(
            array('Magento\Core\Model\Object'),
            $this->injectable->getDependencies($this->fileReflection)
        );
    }

    /**
     * Covered getDependencies
     *
     * @test
     */
    public function testGetDependenciesWithException()
    {
        $this->parameterReflection->expects($this->once())->method('getClass')->will(
            $this->returnCallback(
                function () {
                    throw new \ReflectionException('Class Magento\Core\Model\Object does not exist');
                }
            )
        );

        $this->assertEquals(
            array('Magento\Core\Model\Object'),
            $this->injectable->getDependencies($this->fileReflection)
        );
    }

    /**
     * Covered with some different exception method
     *
     * @test
     * @expectedException \ReflectionException
     */
    public function testGetDependenciesWithOtherException()
    {
        $this->parameterReflection->expects($this->once())->method('getClass')->will(
            $this->returnCallback(
                function () {
                    throw new \ReflectionException('Some message');
                }
            )
        );

        $this->injectable->getDependencies($this->fileReflection);
    }

    /**
     * Covered when method declared in parent class
     *
     * @test
     */
    public function testGetDependenciesWhenMethodDeclaredInParentClass()
    {
        $this->declaredClass->expects($this->once())->method('getName')->will($this->returnValue('ParentClass'));

        $this->injectable->getDependencies($this->fileReflection);
    }
}
