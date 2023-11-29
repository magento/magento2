<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Rss;

use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Config\Model\Config as ConfigModel;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface as ConfigResource;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[
    AppArea(Area::AREA_ADMINHTML),
    DbIsolation(false),
]
class CategoryTest extends TestCase
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtureStorage;

    /**
     * @var Category
     */
    private $model;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    protected function setUp(): void
    {
        $configModel = Bootstrap::getObjectManager()->create(ConfigModel::class);
        $configModel->setDataByPath('rss/catalog/category', 1);
        $configModel->save();
        $indexerRegistry = Bootstrap::getObjectManager()->get(IndexerRegistry::class);
        $indexerRegistry->get('catalogsearch_fulltext')->reindexAll();

        $this->fixtureStorage = DataFixtureStorageManager::getStorage();
        $this->model = Bootstrap::getObjectManager()->create(Category::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
    }

    protected function tearDown(): void
    {
        $configResource = Bootstrap::getObjectManager()->get(ConfigResource::class);
        $configResource->deleteConfig('rss/catalog/category');
    }

    #[
        DataFixture(CategoryFixture::class, as: 'c1'),
        DataFixture(ProductFixture::class, ['sku' => 'p1', 'category_ids' => ['$c1.id$']], 'p1'),
    ]
    public function testGetProductCollection(): void
    {
        $category = $this->fixtureStorage->get('c1');
        $store = $this->storeManager->getStore('default');
        $productCollection = $this->model->getProductCollection($category, $store->getId());
        self::assertEquals(1, $productCollection->count());
        $product = $productCollection->getFirstItem();
        self::assertEquals('p1', $product->getSku());
    }
}
