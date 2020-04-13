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

    protected function setUp()
    {
        $this->identityValidator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(IdentityValidator::class);
    }

    public function testIsValid()
    {
        $isValid = $this->identityValidator->isValid(self::VALID_UUID);
        $this->assertEquals(true, $isValid);
    }

    public function testIsNotValid()
    {
        $isValid = $this->identityValidator->isValid(self::INVALID_UUID);
        $this->assertEquals(false, $isValid);
    }

    public function testEmptyValue()
    {
        $isValid = $this->identityValidator->isValid('');
        $this->assertEquals(false, $isValid);
    }
}
