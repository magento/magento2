<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Encryption\Test\Unit;

use Magento\Framework\Encryption\KeyValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class KeyValidatorTest extends TestCase
{
    /**
     * @var KeyValidator
     */
    private $keyValidator;

    protected function setUp(): void
    {
        $this->keyValidator = (new ObjectManager($this))->getObject(KeyValidator::class);
    }

    /**
     * @param $key
     * @param bool $expected
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($key, $expected = true)
    {
        $this->assertEquals($expected, $this->keyValidator->isValid($key));
    }

    /**
     * @return array
     */
    public static function isValidDataProvider() : array
    {
        return [
            '32 numbers' => ['12345678901234567890123456789012'],
            '32 characters' => ['aBcdeFghIJKLMNOPQRSTUvwxYzabcdef'],
            '32 special characters' => ['!@#$%^&*()_+~`:;"<>,.?/|*&^%$#@!'],
            '32 combination' =>['1234eFghI1234567^&*(890123456789'],
            'empty string' => ['', false],
            'leading space' => [' 1234567890123456789012345678901', false],
            'tailing space' => ['1234567890123456789012345678901 ', false],
            'space in the middle' => ['12345678901 23456789012345678901', false],
            'tab in the middle' => ['12345678901    23456789012345678', false],
            'return in the middle' => ['12345678901
            23456789012345678901', false],
            '31 characters' => ['1234567890123456789012345678901', false],
            '33 characters' => ['123456789012345678901234567890123', false],
        ];
    }
}
