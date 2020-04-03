<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for customer authentication model.
 *
 * @magentoDbIsolation enabled
 */
class AuthenticationTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Authentication */
    private $authentication;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->authentication = $this->objectManager->get(Authentication::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/locked_customer.php
     *
     * @return void
     */
    public function testAuthenticateWithWrongPasswordByLockedCustomer(): void
    {
        $this->expectExceptionObject(new UserLockedException(__('The account is locked.')));
        $this->authentication->authenticate(1, 'password1');
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/expired_lock_for_customer.php
     *
     * @return void
     */
    public function testCustomerAuthenticate(): void
    {
        $this->assertTrue($this->authentication->authenticate(1, 'password'));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/expired_lock_for_customer.php
     *
     * @return void
     */
    public function testCustomerAuthenticateWithWrongPassword(): void
    {
        $this->expectExceptionObject(new InvalidEmailOrPasswordException(__('Invalid login or password.')));
        $this->authentication->authenticate(1, 'password1');
    }
}
