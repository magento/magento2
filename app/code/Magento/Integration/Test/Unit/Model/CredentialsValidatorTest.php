<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model;

/**
 * Unit test for \Magento\Integration\Model\CredentialsValidator
 */
class CredentialsValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Integration\Model\CredentialsValidator
     */
    protected $credentialsValidator;

    protected function setUp(): void
    {
        $this->credentialsValidator = new \Magento\Integration\Model\CredentialsValidator();
    }

    /**
     */
    public function testValidateNoUsername()
    {
        $this->expectException(\Magento\Framework\Exception\InputException::class);
        $this->expectExceptionMessage('"username" is required. Enter and try again.');

        $username = '';
        $password = 'my_password';

        $this->credentialsValidator->validate($username, $password);
    }

    /**
     */
    public function testValidateNoPassword()
    {
        $this->expectException(\Magento\Framework\Exception\InputException::class);
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
