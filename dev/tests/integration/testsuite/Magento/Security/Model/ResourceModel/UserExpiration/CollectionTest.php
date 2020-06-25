<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Security\Model\ResourceModel\UserExpiration;

/**
 * Test UserExpiration collection filters.
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Security\Model\ResourceModel\UserExpiration\CollectionFactory
     */
    protected $collectionModelFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->collectionModelFactory = $this->objectManager
            ->create(\Magento\Security\Model\ResourceModel\UserExpiration\CollectionFactory::class);
    }

    /**
     * @magentoDataFixture Magento/Security/_files/expired_users.php
     */
    public function testAddExpiredActiveUsersFilter()
    {
        /** @var \Magento\Security\Model\ResourceModel\UserExpiration\Collection $collectionModel */
        $collectionModel = $this->collectionModelFactory->create();
        $collectionModel->addActiveExpiredUsersFilter();
        static::assertEquals(1, $collectionModel->getSize());
    }

    /**
     * @magentoDataFixture Magento/Security/_files/expired_users.php
     */
    public function testAddUserIdsFilter()
    {
        $adminUserNameFromFixture = 'adminUserExpired';
        $user = $this->objectManager->create(\Magento\User\Model\User::class);
        $user->loadByUsername($adminUserNameFromFixture);

        /** @var \Magento\Security\Model\ResourceModel\UserExpiration\Collection $collectionModel */
        $collectionModel = $this->collectionModelFactory->create()->addUserIdsFilter([$user->getId()]);
        static::assertEquals(1, $collectionModel->getSize());
    }

    /**
     * @magentoDataFixture Magento/Security/_files/expired_users.php
     */
    public function testAddExpiredRecordsForUserFilter()
    {
        $adminUserNameFromFixture = 'adminUserExpired';
        $user = $this->objectManager->create(\Magento\User\Model\User::class);
        $user->loadByUsername($adminUserNameFromFixture);

        /** @var \Magento\Security\Model\ResourceModel\UserExpiration\Collection $collectionModel */
        $collectionModel = $this->collectionModelFactory->create()->addExpiredRecordsForUserFilter($user->getId());
        static::assertEquals(1, $collectionModel->getSize());
    }
}
