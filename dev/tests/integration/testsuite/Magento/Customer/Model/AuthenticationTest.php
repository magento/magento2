<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for customer authentication model.
 *
 * @see \Magento\Customer\Model\Authentication
 * @magentoDbIsolation enabled
 */
class AuthenticationTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var AuthenticationInterface */
    private $authentication;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->authentication = $this->objectManager->get(AuthenticationInterface::class);
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
