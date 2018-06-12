<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model\StoreSwitcher;

use Magento\Store\Model\StoreSwitcher;
use Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;

class RewriteUrlTest extends \PHPUnit\Framework\TestCase
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
     * @magentoDataFixture Magento/UrlRewrite/_files/url_rewrite.php
     * @return void
     * @throws StoreSwitcher\CannotSwitchStoreException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSwitchToNonExistingPage()
    {
        $expectedUrl = "http://localhost/";
        $fromStoreCode = 'default';
        /** @var \Magento\Store\Api\StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->create(\Magento\Store\Api\StoreRepositoryInterface::class);
        $fromStore = $storeRepository->get($fromStoreCode);

        $toStoreCode = 'fixture_second_store';
        /** @var \Magento\Store\Api\StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->create(\Magento\Store\Api\StoreRepositoryInterface::class);
        $toStore = $storeRepository->get($toStoreCode);

        $redirectUrl = "http://domain.com/simple-product.html";

        $this->assertEquals($expectedUrl, $this->storeSwitcher->switch($fromStore, $toStore, $redirectUrl));
    }

    /**
     * @magentoDataFixture Magento/UrlRewrite/_files/url_rewrite.php
     * @return void
     * @throws StoreSwitcher\CannotSwitchStoreException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSwitchToExistingPage()
    {
        $expectedUrl = "http://localhost/page-c";
        $fromStoreCode = 'default';
        /** @var \Magento\Store\Api\StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->create(\Magento\Store\Api\StoreRepositoryInterface::class);
        $fromStore = $storeRepository->get($fromStoreCode);

        $toStoreCode = 'fixture_second_store';
        /** @var \Magento\Store\Api\StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->create(\Magento\Store\Api\StoreRepositoryInterface::class);
        $toStore = $storeRepository->get($toStoreCode);

        $redirectUrl = "http://localhost/page-c";

        $this->assertEquals($expectedUrl, $this->storeSwitcher->switch($fromStore, $toStore, $redirectUrl));
    }
}
