<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Catalog\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManager;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Indexer\TestCase;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;

/**
 * Test class for checking migrate store level catalog product to website level
 */
class MoveStoreLevelCatalogDataToWebsiteScopeOnSingleStoreModeTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->config = $this->objectManager->get(ConfigInterface::class);
        parent::setUp();
    }

    /**
     * Test class for checking migration of product from store level scope to website scope in
     * single store mode.
     */
    #[
        DbIsolation(true),
        DataFixture(CategoryFixture::class, ['name' => 'Category1', 'parent_id' => '2'], 'c11'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple_product',
                'name' => 'simple product for all store view',
                'price' => 35,
                'website_ids' => [1],
                'category_ids' => ['$c11.id$']
            ],
            'simple product for all store view'
        ),
        AppArea('adminhtml')
    ]
    public function testExecute(): void
    {
        $eventManager = $this->objectManager->get(ManagerInterface::class);
        $scopeConfig = $this->objectManager->get(ReinitableConfigInterface::class);
        $productFromFixture = $this->fixtures->get('simple product for all store view');

        $product = $this->productRepository->get($productFromFixture->getSku());
        $this->assertEquals($productFromFixture->getName(), $product->getName());
        $this->assertEquals($productFromFixture->getPrice(), $product->getPrice());

        $eventManager->dispatch(
            'admin_system_config_changed_section_general',
            [
                'website' => '',
                'store' => '',
                'changed_paths' => [
                    StoreManager::XML_PATH_SINGLE_STORE_MODE_ENABLED
                ],
            ]
        );

        $product->setName('simple product for default store view')->setStoreId(0);
        $this->productRepository->save($product);

        $product = $this->productRepository->get($productFromFixture->getSku());
        $this->assertEquals('simple product for default store view', $product->getName());

        $this->config->saveConfig('StoreManager::XML_PATH_SINGLE_STORE_MODE_ENABLED', 1);
        $scopeConfig->reinit();

        $product = $this->productRepository->get($productFromFixture->getSku());
        $this->assertEquals('simple product for default store view', $product->getName());
        $this->assertEquals($productFromFixture->getPrice(), $product->getPrice());

        $this->config->saveConfig('StoreManager::XML_PATH_SINGLE_STORE_MODE_ENABLED', 0);
        $scopeConfig->reinit();
    }
}
