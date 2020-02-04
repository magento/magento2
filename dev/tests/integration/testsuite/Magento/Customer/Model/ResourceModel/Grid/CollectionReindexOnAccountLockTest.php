<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\ResourceModel\Grid;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Indexer\TestCase;
use Magento\Tests\NamingConvention\true\mixed;

/**
 * Test if customer account lock on too many failed authentication attempts triggers customer grid reindex
 *
 * @SuppressWarnings(PHPMD)
 */
class CollectionReindexOnAccountLockTest extends TestCase
{
    /**
     * Trigger customer account lock by making 10 failed authentication attempts
     */
    private function lockCustomerAccountWithInvalidAuthentications()
    {
        /** @var AccountManagementInterface */
        $accountManagement = Bootstrap::getObjectManager()->create(AccountManagementInterface::class);

        for ($i = 0; $i < 10; $i++) {
            try {
                $accountManagement->authenticate('customer@example.com', 'wrongPassword');
                // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            } catch (InvalidEmailOrPasswordException $e) {
            }
        }
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    private function getCustomerLockExpire(): ?string
    {
        /** @var CustomerRegistry $customerRegistry */
        $customerRegistry = Bootstrap::getObjectManager()->create(CustomerRegistry::class);
        $customerModel = $customerRegistry->retrieve(1);
        $this->assertNotEmpty($customerModel);

        return $customerModel->getData('lock_expires');
    }

    /**
     * @return mixed
     */
    private function getCustomerGridLockExpire(): ?string
    {
        /** @var Collection */
        $gridCustomerCollection = Bootstrap::getObjectManager()->create(Collection::class);
        $gridCustomerItem = $gridCustomerCollection->getItemById(1);
        $this->assertNotEmpty($gridCustomerItem);

        return $gridCustomerItem->getData('lock_expires');
    }

    /**
     * Test if customer account lock on too many failed authentication attempts triggers customer grid reindex
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     */
    public function testCustomerAccountReindexOnLock()
    {
        $this->assertSame(
            $this->getCustomerGridLockExpire(),
            $this->getCustomerLockExpire()
        );

        $this->lockCustomerAccountWithInvalidAuthentications();

        $this->assertSame(
            $this->getCustomerGridLockExpire(),
            $this->getCustomerLockExpire()
        );
    }
}
