<?php declare(strict_types=1);
/**
 * Integration test for \Magento\Framework\Validator\Factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator\Test\Unit;

use Magento\Framework\Validator\EmailAddress;
use PHPUnit\Framework\TestCase;

class EmailAddressTest extends TestCase
{
    /**
     * Test that the validator ignores TLD validation by default
     */
    public function testDefaultValidateIgnoresTLD()
    {
        /** @var EmailAddress $emailAddress */
        $emailAddress = new EmailAddress();
        $this->assertTrue($emailAddress->isValid("user@domain.unknown"));
    }

    /**
     * Test that the TLD validation can be enabled
     */
    public function testCanValidateTLD()
    {
        /** @var EmailAddress $emailAddress */
        $emailAddress = new EmailAddress();
        $emailAddress->setValidateTld(true);
        $this->assertFalse($emailAddress->isValid("user@domain.unknown"));
    }
}
