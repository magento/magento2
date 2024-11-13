<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test for \Magento\Eav\Model\Validator\Attribute\Code
 */
namespace Magento\Eav\Test\Unit\Model\Validator\Attribute;

use Magento\Eav\Model\Validator\Attribute\Code;
use Magento\Framework\Validator\ValidateException;
use PHPUnit\Framework\TestCase;

class CodeTest extends TestCase
{
    /**
     * Testing \Magento\Eav\Model\Validator\Attribute\Code::isValid
     *
     * @dataProvider isValidDataProvider
     * @param string $attributeCode
     * @param bool $expected
     * @throws ValidateException
     */
    public function testIsValid(string $attributeCode, bool $expected): void
    {
        $validator = new Code();
        $this->assertEquals($expected, $validator->isValid($attributeCode));
    }

    /**
     * Data provider for testIsValid
     *
     * @return array
     */
    public static function isValidDataProvider(): array
    {
        return [
            [
                'Attribute_code',
                true
            ], [
                'attribute_1',
                true
            ],[
                'Attribute_1',
                true
            ], [
                '_attribute_code',
                false
            ], [
                'attribute.code',
                false
            ], [
                '1attribute_code',
                false
            ], [
                'more_than_60_chars_more_than_60_chars_more_than_60_chars_more',
                false
            ], [
                'container_attribute',
                false,
            ],
        ];
    }
}
