<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Measure\Test\Unit;

use Magento\Framework\Measure\Exception\MeasureException;
use Magento\Framework\Measure\Weight;
use PHPUnit\Framework\TestCase;

class WeightTest extends TestCase
{
    /**
     * Test for Weight initialisation.
     */
    public function testWeightInit()
    {
        $value = new Weight('100', Weight::STANDARD, 'en_US');
        $this->assertTrue($value instanceof Weight, 'Weight object not returned');
    }

    /**
     * Test for exception unknown type.
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testWeightUnknownType()
    {
        $this->expectException(MeasureException::class);
        $this->expectExceptionMessage('Type (Weight::UNKNOWN) is unknown');

        $value = new Weight('100', 'Weight::UNKNOWN', 'en_US');
    }

    /**
     * Test for standard locale.
     */
    public function testWeightNoLocale()
    {
        $value = new Weight('100', Weight::STANDARD);
        $this->assertEquals(100, $value->getValue(), 'Weight value expected');
    }

    /**
     * Test for positive value.
     */
    public function testWeightValuePositive()
    {
        $value = new Weight('100', Weight::STANDARD, 'en_US');
        $this->assertEquals(100, $value->getValue(), 'Weight value expected to be a positive integer');
    }

    /**
     * Test for negative value.
     */
    public function testWeightValueNegative()
    {
        $value = new Weight('-100', Weight::STANDARD, 'en_US');
        $this->assertEquals(-100, $value->getValue(), 'Weight value expected to be a negative integer');
    }

    /**
     * Test for decimal value.
     */
    public function testWeightValueDecimal()
    {
        $value = new Weight('-100.200', Weight::STANDARD, 'en_US');
        $this->assertEquals(-100.2, $value->getValue(), 'Weight value expected to be a decimal value');
    }

    /**
     * Test for set positive value.
     */
    public function testWeightSetPositive()
    {
        $value = new Weight('100', Weight::STANDARD, 'en_US');
        $value->setValue('200', Weight::STANDARD, 'en_US');
        $this->assertEquals(200, $value->getValue(), 'Weight value expected to be a positive integer');
    }

    /**
     * Test for set negative value.
     */
    public function testWeightSetNegative()
    {
        $value = new Weight('-100', Weight::STANDARD, 'en_US');
        $value->setValue('-200', Weight::STANDARD, 'en_US');
        $this->assertEquals(-200, $value->getValue(), 'Weight value expected to be a negative integer');
    }

    /**
     * Test for set decimal value.
     */
    public function testWeightSetDecimal()
    {
        $value = new Weight('-100.200', Weight::STANDARD, 'en_US');
        $value->setValue('-200.200', Weight::STANDARD, 'en_US');
        $this->assertEquals(-200.2, $value->getValue(), 'Weight value expected to be a decimal value');
    }

    /**
     * Test for exception unknown locale
     */
    public function testMeasureSetWithNoLocale()
    {
        $this->expectError();

        $value = new Weight('100', Weight::STANDARD, 'en_US');
        $value->setValue('200', Weight::STANDARD);
    }

    /**
     * Test setting type.
     */
    public function testWeightSetType()
    {
        $value = new Weight('-100', Weight::STANDARD, 'en_US');
        $value->setType(Weight::KILOGRAM);
        $this->assertEquals(Weight::KILOGRAM, $value->getType(), 'Weight type expected');
    }

    /**
     * Test setting computed type.
     */
    public function testWeightSetComputedType1()
    {
        $value = new Weight('-100', Weight::KILOGRAM, 'en_US');
        $value->setType(Weight::OUNCE);
        $this->assertEquals(Weight::OUNCE, $value->getType(), 'Weight type expected');
    }

    /**
     * Test setting computed type.
     */
    public function testWeightSetComputedType2()
    {
        $value = new Weight('-100', Weight::OUNCE, 'en_US');
        $value->setType(Weight::KILOGRAM);
        $this->assertEquals(Weight::KILOGRAM, $value->getType(), 'Weight type expected');
    }

    /**
     * Test setting unknown type.
     */
    public function testWeightSetTypeFailed()
    {
        $this->expectException(MeasureException::class);
        $this->expectExceptionMessage('Type (Weight::UNKNOWN) is unknown');

        $value = new Weight('-100', Weight::STANDARD, 'en_US');
        $value->setType('Weight::UNKNOWN');
    }

    /**
     * Test toString
     */
    public function testWeightToString()
    {
        $value = new Weight('-100', Weight::STANDARD, 'en_US');
        $this->assertEquals('-100 kg', $value->toString(), 'Value -100 kg expected');
    }

    /**
     * Test casting of value.
     */
    public function testWeightCastingValue()
    {
        $value = new Weight('-100', Weight::STANDARD, 'en_US');
        $this->assertEquals('-100 kg', (string) $value, 'Value -100 kg expected');
    }

    /**
     * Test getConversionList.
     */
    public function testWeightConversionList()
    {
        $value = new Weight('-100', Weight::STANDARD, 'en_US');
        $unit = $value->getConversionList();
        $this->assertIsArray($unit, 'Array expected');
    }
}
