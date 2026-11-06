<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Test for \Magento\Eav\Model\Validator\Attribute\Code
 */
namespace Magento\Eav\Test\Unit\Model\Validator\Attribute;

use Magento\Eav\Model\Validator\Attribute\Code;
use Magento\Framework\Validator\ValidateException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class CodeTest extends TestCase
{
    /**
     * Testing \Magento\Eav\Model\Validator\Attribute\Code::isValid
     * @param string $attributeCode
     * @param bool $expected
     * @throws ValidateException
     */
    #[DataProvider('isValidDataProvider')]
    public function testIsValid(string $attributeCode, bool $expected): void
    {
        $validator = new Code();
        $this->assertEquals($expected, $validator->isValid($attributeCode));
    }

    /**
     * A validator instance is shared, so messages of an invalid code must not affect the next code.
     *
     * @throws ValidateException
     */
    public function testIsValidAfterInvalidCode(): void
    {
        $validator = new Code();
        $this->assertFalse($validator->isValid('1attribute_code'));
        $this->assertCount(1, $validator->getMessages());

        $this->assertTrue($validator->isValid('attribute_code'));
        $this->assertSame([], $validator->getMessages());

        $this->assertFalse($validator->isValid('attribute.code'));
        $this->assertCount(1, $validator->getMessages());
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
