<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Test\Unit\Api;

use Magento\Framework\Api\SimpleDataObjectConverter;

/**
 * Unit test class for \Magento\Framework\Api\SimpleDataObjectConverter
 */
class SimpleDataObjectConverterTest extends \PHPUnit\Framework\TestCase
{
    public function testCamelCaseToSnakeCase()
    {
        $this->assertEquals("default_shipping", SimpleDataObjectConverter::camelCaseToSnakeCase("defaultShipping"));
    }

    public function testCamelCaseToSnakeCaseWithNumber()
    {
        $this->assertEquals("default_shipping_1", SimpleDataObjectConverter::camelCaseToSnakeCase("defaultShipping1"));
    }

    public function testCamelCaseToSnakeCaseWithMultiNumber()
    {
        $this->assertEquals("default_shipping_123", SimpleDataObjectConverter::camelCaseToSnakeCase("defaultShipping123"));
    }

    public function testCamelCaseToSnakeCaseWithMultiNumberInBetween()
    {
        $this->assertEquals("default_123_shipping_123", SimpleDataObjectConverter::camelCaseToSnakeCase("default123Shipping123"));
    }

    public function testSnakeCaseToCamelCase()
    {
        $this->assertEquals("defaultShipping", SimpleDataObjectConverter::snakeCaseToCamelCase("default_shipping"));
    }

    public function testSnakeCaseToCamelCaseWithNumber()
    {
        $this->assertEquals("defaultShipping1", SimpleDataObjectConverter::snakeCaseToCamelCase("default_shipping_1"));
    }

    public function testSnakeCaseToCamelCaseWithMultiNumber()
    {
        $this->assertEquals("defaultShipping123", SimpleDataObjectConverter::snakeCaseToCamelCase("default_shipping_123"));
    }
}
