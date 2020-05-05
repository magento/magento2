<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model;

use Magento\Integration\Model\CredentialsValidator;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Integration\Model\CredentialsValidator
 */
class CredentialsValidatorTest extends TestCase
{
    /**
     * @var CredentialsValidator
     */
    protected $credentialsValidator;

    protected function setUp(): void
    {
        $this->credentialsValidator = new CredentialsValidator();
    }

    public function testValidateNoUsername()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('"username" is required. Enter and try again.');
        $username = '';
        $password = 'my_password';

        $this->credentialsValidator->validate($username, $password);
    }

    public function testValidateNoPassword()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('"password" is required. Enter and try again.');
        $username = 'my_username';
        $password = '';

        $this->credentialsValidator->validate($username, $password);
    }

    public function testValidateValidCredentials()
    {
        $username = 'my_username';
        $password = 'my_password';

        $result = $this->credentialsValidator->validate($username, $password);
        $this->assertNull($result);
    }
}
