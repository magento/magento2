<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Test\Unit\Data;

use Magento\Framework\Api\AttributeValue;

class AttributeValueTest extends \PHPUnit_Framework_TestCase
{
    const ATTRIBUTE_CODE = 'ATTRIBUTE_CODE';

    const STRING_VALUE = 'VALUE';

    const INTEGER_VALUE = 1;

    const FLOAT_VALUE = 1.0;

    const BOOLEAN_VALUE = true;

    public function testConstructorAndGettersWithString()
    {
        $attribute = new AttributeValue(
            [
                AttributeValue::ATTRIBUTE_CODE => self::ATTRIBUTE_CODE,
                AttributeValue::VALUE => self::STRING_VALUE
            ]
        );

        $this->assertSame(self::ATTRIBUTE_CODE, $attribute->getAttributeCode());
        $this->assertSame(self::STRING_VALUE, $attribute->getValue());
    }

    public function testConstructorAndGettersWithInteger()
    {
        $attribute = new AttributeValue(
            [
                AttributeValue::ATTRIBUTE_CODE => self::ATTRIBUTE_CODE,
                AttributeValue::VALUE => self::INTEGER_VALUE
            ]
        );

        $this->assertSame(self::ATTRIBUTE_CODE, $attribute->getAttributeCode());
        $this->assertSame(self::INTEGER_VALUE, $attribute->getValue());
    }

    public function testConstructorAndGettersWithFloat()
    {
        $attribute = new AttributeValue(
            [
                AttributeValue::ATTRIBUTE_CODE => self::ATTRIBUTE_CODE,
                AttributeValue::VALUE => self::FLOAT_VALUE
            ]
        );

        $this->assertSame(self::ATTRIBUTE_CODE, $attribute->getAttributeCode());
        $this->assertSame(self::FLOAT_VALUE, $attribute->getValue());
    }

    public function testConstructorAndGettersWithBoolean()
    {
        $attribute = new AttributeValue(
            [
                AttributeValue::ATTRIBUTE_CODE => self::ATTRIBUTE_CODE,
                AttributeValue::VALUE => self::BOOLEAN_VALUE
            ]
        );

        $this->assertSame(self::ATTRIBUTE_CODE, $attribute->getAttributeCode());
        $this->assertSame(self::BOOLEAN_VALUE, $attribute->getValue());
    }
}
