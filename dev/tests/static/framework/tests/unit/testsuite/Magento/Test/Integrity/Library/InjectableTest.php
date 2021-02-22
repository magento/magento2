<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Library;

use Magento\TestFramework\Integrity\Library\Injectable;

/**
 */
class InjectableTest extends \PHPUnit\Framework\TestCase
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
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $parameterReflection;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $declaredClass;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->injectable = new Injectable();
        $this->fileReflection = $this->getMockBuilder(
            \Zend\Code\Reflection\FileReflection::class
        )->disableOriginalConstructor()->getMock();

        $classReflection = $this->getMockBuilder(
            \Zend\Code\Reflection\ClassReflection::class
        )->disableOriginalConstructor()->getMock();

        $methodReflection = $this->getMockBuilder(
            \Zend\Code\Reflection\MethodReflection::class
        )->disableOriginalConstructor()->getMock();

        $this->parameterReflection = $this->getMockBuilder(
            \Zend\Code\Reflection\ParameterReflection::class
        )->disableOriginalConstructor()->getMock();

        $this->declaredClass = $this->getMockBuilder(
            \Zend\Code\Reflection\ClassReflection::class
        )->disableOriginalConstructor()->getMock();

        $methodReflection->expects(
            $this->once()
        )->method(
            'getDeclaringClass'
        )->willReturn(
            $this->declaredClass
        );

        $methodReflection->expects(
            $this->any()
        )->method(
            'getParameters'
        )->willReturn(
            [$this->parameterReflection]
        );

        $classReflection->expects(
            $this->once()
        )->method(
            'getMethods'
        )->willReturn(
            [$methodReflection]
        );

        $this->fileReflection->expects(
            $this->once()
        )->method(
            'getClasses'
        )->willReturn(
            [$classReflection]
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
            \Zend\Code\Reflection\ClassReflection::class
        )->disableOriginalConstructor()->getMock();

        $classReflection->expects(
            $this->once()
        )->method(
            'getName'
        )->willReturn(
            \Magento\Core\Model\Object::class
        );

        $this->parameterReflection->expects(
            $this->once()
        )->method(
            'getClass'
        )->willReturn(
            $classReflection
        );

        $this->assertEquals(
            [\Magento\Core\Model\Object::class],
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
        $this->parameterReflection->expects($this->once())->method('getClass')->willReturnCallback(
            
                function () {
                    throw new \ReflectionException('Class Magento\Core\Model\Object does not exist');
                }
            
        );

        $this->assertEquals(
            [\Magento\Core\Model\Object::class],
            $this->injectable->getDependencies($this->fileReflection)
        );
    }

    /**
     * Covered with some different exception method
     *
     * @test
     */
    public function testGetDependenciesWithOtherException()
    {
        $this->expectException(\ReflectionException::class);

        $this->parameterReflection->expects($this->once())->method('getClass')->willReturnCallback(
            
                function () {
                    throw new \ReflectionException('Some message');
                }
            
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
        $this->declaredClass->expects($this->once())->method('getName')->willReturn('ParentClass');

        $this->injectable->getDependencies($this->fileReflection);
    }
}
