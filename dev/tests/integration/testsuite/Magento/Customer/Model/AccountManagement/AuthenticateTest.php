<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\AccountManagement;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for customer authenticate via customer account management service.
 *
 * @magentoDbIsolation enabled
 */
class AuthenticateTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var AccountManagementInterface */
    private $accountManagement;

    /** @var CustomerRegistry */
    private $customerRegistry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->accountManagement = $this->objectManager->get(AccountManagementInterface::class);
        $this->customerRegistry = $this->objectManager->get(CustomerRegistry::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/locked_customer.php
     *
     * @return void
     */
    public function testAuthenticateByLockedCustomer(): void
    {
        $this->expectExceptionObject(new UserLockedException(__('The account is locked.')));
        $this->accountManagement->authenticate('customer@example.com', 'password');
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/expired_lock_for_customer.php
     *
     * @return void
     */
    public function testAuthenticateByCustomerExpiredLock(): void
    {
        $email = 'customer@example.com';
        $customer = $this->accountManagement->authenticate($email, 'password');
        $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
        $this->assertEquals(0, $customerSecure->getFailuresNum());
        $this->assertNull($customerSecure->getFirstFailure());
        $this->assertNull($customerSecure->getLockExpires());
    }
}
