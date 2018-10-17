<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model\StoreSwitcher;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
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
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Class dependencies initialization
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeSwitcher = $this->objectManager->get(StoreSwitcher::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/UrlRewrite/_files/url_rewrite.php
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     * @return void
     * @throws StoreSwitcher\CannotSwitchStoreException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSwitchToNonExistingPage()
    {
        $fromStoreCode = 'default';
        /** @var \Magento\Store\Api\StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->create(\Magento\Store\Api\StoreRepositoryInterface::class);
        $fromStore = $storeRepository->get($fromStoreCode);

        $toStoreCode = 'fixture_second_store';
        /** @var \Magento\Store\Api\StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->create(\Magento\Store\Api\StoreRepositoryInterface::class);
        $toStore = $storeRepository->get($toStoreCode);

        $this->setBaseUrl($toStore);

        $product = $this->productRepository->get('simple333');

        $redirectUrl = "http://domain.com/{$product->getUrlKey()}.html";
        $expectedUrl = $toStore->getBaseUrl();

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
        $fromStoreCode = 'default';
        /** @var \Magento\Store\Api\StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->create(\Magento\Store\Api\StoreRepositoryInterface::class);
        $fromStore = $storeRepository->get($fromStoreCode);

        $toStoreCode = 'fixture_second_store';
        /** @var \Magento\Store\Api\StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->create(\Magento\Store\Api\StoreRepositoryInterface::class);
        $toStore = $storeRepository->get($toStoreCode);

        $redirectUrl = $expectedUrl = "http://localhost/page-c";

        $this->assertEquals($expectedUrl, $this->storeSwitcher->switch($fromStore, $toStore, $redirectUrl));
    }

    /**
     * Set base url to store.
     *
     * @param StoreInterface $targetStore
     * @return void
     */
    private function setBaseUrl(StoreInterface $targetStore)
    {
        $configValue = $this->objectManager->create(Value::class);
        $configValue->load('web/unsecure/base_url', 'path');
        $baseUrl = 'http://domain.com/';
        if (!$configValue->getPath()) {
            $configValue->setPath('web/unsecure/base_url');
        }
        $configValue->setValue($baseUrl);
        $configValue->setScope(ScopeInterface::SCOPE_STORES);
        $configValue->setScopeId($targetStore->getId());
        $configValue->save();

        $reinitibleConfig = $this->objectManager->create(ReinitableConfigInterface::class);
        $reinitibleConfig->reinit();
    }
}
