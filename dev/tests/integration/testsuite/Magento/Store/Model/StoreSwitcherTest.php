<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;

class StoreSwitcherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StoreSwitcher
     */
    private $storeSwitcher;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Class dependencies initialization
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeSwitcher = $this->objectManager->get(StoreSwitcher::class);
    }

    /**
     * @magentoDataFixture Magento/Store/_files/store.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @return void
     * @throws StoreSwitcher\CannotSwitchStoreException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSwitch()
    {
        $redirectUrl = "http://domain.com/?SID=e5h3e086dce3ckkqt9ia7avl27&___store=fixture_second_store";
        $expectedUrl = "http://domain.com/";
        $fromStoreCode = 'test';
        /** @var \Magento\Store\Api\StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->create(\Magento\Store\Api\StoreRepositoryInterface::class);
        $fromStore = $storeRepository->get($fromStoreCode);

        $toStoreCode = 'fixture_second_store';
        /** @var \Magento\Store\Api\StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->create(\Magento\Store\Api\StoreRepositoryInterface::class);
        $toStore = $storeRepository->get($toStoreCode);

        $this->assertEquals($expectedUrl, $this->storeSwitcher->switch($fromStore, $toStore, $redirectUrl));
    }
}
