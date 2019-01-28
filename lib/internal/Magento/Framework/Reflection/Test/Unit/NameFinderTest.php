<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreStart
namespace Magento\Framework\Reflection\Test\Unit;

use Zend\Code\Reflection\ClassReflection;

/**
 * NameFinder Unit Test
 */
class NameFinderTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Reflection\NameFinder */
    protected $nameFinder;

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $this->nameFinder = new \Magento\Framework\Reflection\NameFinder();
    }

    public function testGetSetterMethodName()
    {
        $class = new ClassReflection(\Magento\Framework\Reflection\Test\Unit\DataObject::class);
        $setterName = $this->nameFinder->getSetterMethodName($class, 'AttrName');
        $this->assertEquals("setAttrName", $setterName);

        $booleanSetterName = $this->nameFinder->getSetterMethodName($class, 'Active');
        $this->assertEquals("setIsActive", $booleanSetterName);
    }

    /**
     * @codingStandardsIgnoreStart
     * @codingStandardsIgnoreEnd
     */
    public function testGetSetterMethodNameInvalidAttribute()
    {
        $this->setExpectedException(\Exception::class, 'Property "InvalidAttribute" does not have accessor method "setInvalidAttribute" in class "Magento\\Framework\\Reflection\\Test\\Unit\\DataObject"');

        $class = new ClassReflection(\Magento\Framework\Reflection\Test\Unit\DataObject::class);
        $this->nameFinder->getSetterMethodName($class, 'InvalidAttribute');
    }

    /**
     * @codingStandardsIgnoreStart
     * @codingStandardsIgnoreEnd
     */
    public function testGetSetterMethodNameWrongCamelCasedAttribute()
    {
        $this->setExpectedException(\Exception::class, 'Property "ActivE" does not have accessor method "setActivE" in class "Magento\\Framework\\Reflection\\Test\\Unit\\DataObject"');

        $class = new ClassReflection(\Magento\Framework\Reflection\Test\Unit\DataObject::class);
        $this->nameFinder->getSetterMethodName($class, 'ActivE');
    }

    /**
     */
    public function testFindAccessorMethodName()
    {
        $this->setExpectedException(\LogicException::class, 'Property "Property" does not have accessor method "getProperty" in class "className".');

        $reflectionClass = $this->createMock(\Zend\Code\Reflection\ClassReflection::class);
        $reflectionClass->expects($this->atLeastOnce())->method('hasMethod')->willReturn(false);
        $reflectionClass->expects($this->atLeastOnce())->method('getName')->willReturn('className');

        $this->nameFinder->findAccessorMethodName(
            $reflectionClass,
            'Property',
            'getProperty',
            'isProperty'
        );
    }
}
