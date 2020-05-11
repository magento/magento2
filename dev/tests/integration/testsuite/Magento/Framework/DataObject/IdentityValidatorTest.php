<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject;

class IdentityValidatorTest extends \PHPUnit\Framework\TestCase
{
    const VALID_UUID = 'fe563e12-cf9d-4faf-82cd-96e011b557b7';
    const INVALID_UUID = 'abcdef';

    /**
     * @var IdentityValidator
     */
    protected $identityValidator;

    protected function setUp(): void
    {
        $this->identityValidator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(IdentityValidator::class);
    }

    public function testIsValid()
    {
        $isValid = $this->identityValidator->isValid(self::VALID_UUID);
        $this->assertTrue($isValid);
    }

    public function testIsNotValid()
    {
        $isValid = $this->identityValidator->isValid(self::INVALID_UUID);
        $this->assertFalse($isValid);
    }

    public function testEmptyValue()
    {
        $isValid = $this->identityValidator->isValid('');
        $this->assertFalse($isValid);
    }
}
