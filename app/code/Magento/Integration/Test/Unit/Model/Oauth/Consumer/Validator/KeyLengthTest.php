<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Model\Oauth\Consumer\Validator;

use Magento\Integration\Model\Oauth\Consumer\Validator\KeyLength;

class KeyLengthTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Integration\Model\Oauth\Consumer\Validator\KeyLength
     */
    protected $keyLengthValidator;

    protected function setUp()
    {
        $this->keyLengthValidator = new KeyLength();
    }

    public function testSetLength()
    {
        $this->keyLengthValidator->setLength(10);
        $this->assertEquals(10, $this->keyLengthValidator->getLength());
        $this->assertEquals(10, $this->keyLengthValidator->getMin());
        $this->assertEquals(10, $this->keyLengthValidator->getMax());
    }

    public function testIsValid()
    {
        $this->keyLengthValidator->setLength(32);
        $invalidToken = 'asjdkhbcaklsjhlkasjdhlkajhsdljahksdlkafjsljdhskjhksj';
        $this->keyLengthValidator->isValid($invalidToken);
        $expected = ['stringLengthTooLong' => "'{$invalidToken}' is more than 32 characters long"];
        $this->assertEquals($expected, $this->keyLengthValidator->getMessages());

        $invalidToken = 'fajdhkahkjha';
        $this->keyLengthValidator->isValid($invalidToken);
        $expected = ['stringLengthTooShort' => "'{$invalidToken}' is less than 32 characters long"];
        $this->assertEquals($expected, $this->keyLengthValidator->getMessages());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid type given. String expected
     */
    public function testIsValidInvalidType()
    {
        $invalidTokenType = 1;
        $this->keyLengthValidator->isValid($invalidTokenType);
    }
}
