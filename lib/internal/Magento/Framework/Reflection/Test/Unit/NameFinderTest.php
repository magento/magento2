<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

// @codingStandardsIgnoreStart
namespace Magento\Framework\Reflection\Test\Unit;

use Laminas\Code\Reflection\ClassReflection;
use Magento\Framework\Reflection\NameFinder;
use PHPUnit\Framework\TestCase;

/**
 * NameFinder Unit Test
 */
class NameFinderTest extends TestCase
{
    /** @var NameFinder */
    protected $nameFinder;

    /**
     * Set up helper.
     */
    protected function setUp(): void
    {
        $this->nameFinder = new NameFinder();
    }

    public function testGetSetterMethodName()
    {
        $class = new ClassReflection(DataObject::class);
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
        $this->expectException('Exception');
        $this->expectExceptionMessage(
            'Property "InvalidAttribute" does not have accessor method "setInvalidAttribute" '
            . 'in class "Magento\Framework\Reflection\Test\Unit\DataObject"'
        );
        $class = new ClassReflection(DataObject::class);
        $this->nameFinder->getSetterMethodName($class, 'InvalidAttribute');
    }

    /**
     * @codingStandardsIgnoreStart
     * @codingStandardsIgnoreEnd
     */
    public function testGetSetterMethodNameWrongCamelCasedAttribute()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage(
            'Property "ActivE" does not have accessor method "setActivE" '
            . 'in class "Magento\Framework\Reflection\Test\Unit\DataObject"'
        );
        $class = new ClassReflection(DataObject::class);
        $this->nameFinder->getSetterMethodName($class, 'ActivE');
    }

    public function testFindAccessorMethodName()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage(
            'Property "Property" does not have accessor method "getProperty" in class "className".'
        );
        $reflectionClass = $this->createMock(ClassReflection::class);
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
