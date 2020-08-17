<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\Oauth\Consumer\Validator;

use Magento\Integration\Model\Oauth\Consumer\Validator\KeyLength;
use PHPUnit\Framework\TestCase;

class KeyLengthTest extends TestCase
{
    /**
     * Sample length
     */
    const KEY_LENGTH = 32;

    /**
     * @var KeyLength
     */
    protected $keyLengthValidator;

    protected function setUp(): void
    {
        $options = ['length' => KeyLengthTest::KEY_LENGTH];
        $this->keyLengthValidator = new KeyLength($options);
    }

    public function testSetLength()
    {
        $this->assertEquals(KeyLengthTest::KEY_LENGTH, $this->keyLengthValidator->getLength());
        $this->assertEquals(KeyLengthTest::KEY_LENGTH, $this->keyLengthValidator->getMin());
        $this->assertEquals(KeyLengthTest::KEY_LENGTH, $this->keyLengthValidator->getMax());
    }

    public function testIsValidLong()
    {
        $invalidToken = 'asjdkhbcaklsjhlkasjdhlkajhsdljahksdlkafjsljdhskjhksj';
        $this->keyLengthValidator->isValid($invalidToken);
        $expected = ['stringLengthTooLong' => "Key '{$invalidToken}' is more than 32 characters long"];
        $this->assertEquals($expected, $this->keyLengthValidator->getMessages());
    }

    public function testIsValidShort()
    {
        $invalidToken = 'fajdhkahkjha';
        $this->keyLengthValidator->isValid($invalidToken);
        $expected = ['stringLengthTooShort' => "Key '{$invalidToken}' is less than 32 characters long"];
        $this->assertEquals($expected, $this->keyLengthValidator->getMessages());
    }

    public function testIsValidShortCustomKeyName()
    {
        $invalidToken = 'fajdhkahkjha';
        $this->keyLengthValidator->setName('Custom Key');
        $this->keyLengthValidator->isValid($invalidToken);
        $expected = ['stringLengthTooShort' => "Custom Key '{$invalidToken}' is less than 32 characters long"];
        $this->assertEquals($expected, $this->keyLengthValidator->getMessages());
    }

    public function testIsValidInvalidType()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid type given for Key. String expected');
        $invalidTokenType = 1;
        $this->keyLengthValidator->isValid($invalidTokenType);
    }
}
