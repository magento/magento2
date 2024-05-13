<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Measure\Test\Unit;

use Magento\Framework\Measure\Exception\MeasureException;
use Magento\Framework\Measure\Length;
use PHPUnit\Framework\TestCase;

class LengthTest extends TestCase
{
    /**
     * Test for Length initialisation.
     */
    public function testLengthInit()
    {
        $value = new Length('100', Length::STANDARD, 'en_US');
        $this->assertTrue($value instanceof Length, 'Length object not returned');
    }

    /**
     * Test for exception unknown type.
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testLengthUnknownType()
    {
        $this->expectException(MeasureException::class);
        $this->expectExceptionMessage('Type (Length::UNKNOWN) is unknown');

        $value = new Length('100', 'Length::UNKNOWN', 'en_US');
    }

    /**
     * Test for standard locale.
     */
    public function testLengthNoLocale()
    {
        $value = new Length('100', Length::STANDARD);
        $this->assertEquals(100, $value->getValue(), 'Length value expected');
    }

    /**
     * Test for positive value.
     */
    public function testLengthValuePositive()
    {
        $value = new Length('100', Length::STANDARD, 'en_US');
        $this->assertEquals(100, $value->getValue(), 'Length value expected to be a positive integer');
    }

    /**
     * Test for negative value.
     */
    public function testLengthValueNegative()
    {
        $value = new Length('-100', Length::STANDARD, 'en_US');
        $this->assertEquals(-100, $value->getValue(), 'Length value expected to be a negative integer');
    }

    /**
     * Test for decimal value.
     */
    public function testLengthValueDecimal()
    {
        $value = new Length('-100.200', Length::STANDARD, 'en_US');
        $this->assertEquals(-100.2, $value->getValue(), 'Length value expected to be a decimal value');
    }

    /**
     * Test for set positive value.
     */
    public function testLengthSetPositive()
    {
        $value = new Length('100', Length::STANDARD, 'en_US');
        $value->setValue('200', Length::STANDARD, 'en_US');
        $this->assertEquals(200, $value->getValue(), 'Length value expected to be a positive integer');
    }

    /**
     * Test for set negative value.
     */
    public function testLengthSetNegative()
    {
        $value = new Length('-100', Length::STANDARD, 'en_US');
        $value->setValue('-200', Length::STANDARD, 'en_US');
        $this->assertEquals(-200, $value->getValue(), 'Length value expected to be a negative integer');
    }

    /**
     * Test for set decimal value.
     */
    public function testLengthSetDecimal()
    {
        $value = new Length('-100.200', Length::STANDARD, 'en_US');
        $value->setValue('-200.200', Length::STANDARD, 'en_US');
        $this->assertEquals(-200.2, $value->getValue(), 'Length value expected to be a decimal value');
    }

    /**
     * Test for exception unknown locale
     */
    public function testMeasureSetWithNoLocale()
    {
        $this->expectError();

        $value = new Length('100', Length::STANDARD, 'en_US');
        $value->setValue('200', Length::STANDARD);
    }

    /**
     * Test setting type.
     */
    public function testLengthSetType()
    {
        $value = new Length('-100', Length::CENTIMETER, 'en_US');
        $value->setType(Length::INCH);
        $this->assertEquals(Length::INCH, $value->getType(), 'Length type expected');
    }

    /**
     * Test setting computed type.
     */
    public function testLengthSetComputedType1()
    {
        $value = new Length('-100', Length::CENTIMETER, 'en_US');
        $value->setType(Length::INCH);
        $this->assertEquals(Length::INCH, $value->getType(), 'Length type expected');
    }

    /**
     * Test setting computed type.
     */
    public function testLengthSetComputedType2()
    {
        $value = new Length('-100', Length::INCH, 'en_US');
        $value->setType(Length::METER);
        $this->assertEquals(Length::METER, $value->getType(), 'Length type expected');
    }

    /**
     * Test setting unknown type.
     */
    public function testLengthSetTypeFailed()
    {
        $this->expectException(MeasureException::class);
        $this->expectExceptionMessage('Type (Length::UNKNOWN) is unknown');

        $value = new Length('-100', Length::STANDARD, 'en_US');
        $value->setType('Length::UNKNOWN');
    }

    /**
     * Test toString
     */
    public function testLengthToString()
    {
        $value = new Length('-100', Length::CENTIMETER, 'en_US');
        $this->assertEquals('-100 cm', $value->toString(), 'Value -100 cm expected');
    }

    /**
     * Test casting of value.
     */
    public function testLengthCastingValue()
    {
        $value = new Length('-100', Length::INCH, 'en_US');
        $this->assertEquals('-100 in', (string) $value, 'Value -100 in expected');
    }

    /**
     * Test getConversionList.
     */
    public function testLengthConversionList()
    {
        $value = new Length('-100', Length::STANDARD, 'en_US');
        $unit = $value->getConversionList();
        $this->assertIsArray($unit, 'Array expected');
    }
}
