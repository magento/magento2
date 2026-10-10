<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\CategoryLinkRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryProductLinkInterface;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\Area;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\Store;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Fixture\ScopeFixture;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AppArea('crontab')]
#[DbIsolation(false)]
class CategoryLinkRepositoryTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CategoryLinkRepositoryInterface
     */
    private $categoryLinkRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var DataFixtureStorageManager
     */
    private $fixtures;

    /**
     * @var Emulation
     */
    private $appEmulation;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryLinkRepository = $this->objectManager->get(CategoryLinkRepositoryInterface::class);
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $this->appEmulation = $this->objectManager->get(Emulation::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
        parent::setUp();
    }

    /**
     * Make sure that if some custom code uses emulation and assigns products to categories,
     * the categories are not overwritten by loading data via category repository from wrong store.
     * The default store is used in
     * @see \Magento\Catalog\Model\CategoryLinkRepository::save
     *
     */
    #[
        DataFixture(ScopeFixture::class, ['code' => 'default'], as: 'default_store'),
        DataFixture(
            StoreFixture::class,
            ['code' => 'fixture_second_store', 'name' => 'Fixture Store', 'sort_order' => 10],
            as: 'second_store'
        ),
        DataFixture(
            CategoryFixture::class,
            [
                'name' => 'Test Category In Default Store',
                'description' => 'Test description in default store',
                'is_anchor' => 1,
                'parent_id' => 2,
                'available_sort_by' => ['name'],
                'default_sort_by' => 'name',
                'is_active' => true,
            ],
            as: 'category',
            scope: 'default_store'
        ),
        DataFixture(
            CategoryFixture::class,
            [
                'id' => '$category.id$',
                'name' => 'Test Category In Second Store',
                'description' => 'Test description in second store',
            ],
            scope: 'second_store'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product116',
                'sku' => 'simple116',
                'price' => 10,
                'website_ids' => ['$second_store.website_id$'],
            ],
            as: 'product',
            scope: 'default_store'
        ),
    ]
    public function testCategoryDataNotChanged()
    {
        $secondStore = $this->fixtures->get('second_store');
        $category = $this->fixtures->get('category');
        $product = $this->fixtures->get('product');
        $secondStoreId = (int)$secondStore->getId();
        $categoryId = (int)$category->getId();
        /** @var CategoryProductLinkInterface $categoryProductLink */
        $categoryProductLink = $this->objectManager->create(CategoryProductLinkInterface::class);

        $this->appEmulation->startEnvironmentEmulation($secondStoreId, Area::AREA_FRONTEND, true);

        $categoryProductLink
            ->setCategoryId($categoryId)
            ->setSku($product->getSku())
            ->setPosition(2);

        $this->categoryLinkRepository->save($categoryProductLink);

        $this->appEmulation->stopEnvironmentEmulation();

        $categoryFromDefaultStore = $this->categoryRepository->get($categoryId, Store::DEFAULT_STORE_ID);
        $categoryFromSecondStore = $this->categoryRepository->get($categoryId, $secondStoreId);

        $categoryNameFromDefaultStore = $categoryFromDefaultStore->getName();
        $categoryDescriptionFromDefaultStore = $categoryFromDefaultStore->getDescription();

        $categoryNameFromSecondStore = $categoryFromSecondStore->getName();
        $categoryDescriptionFromSecondStore = $categoryFromSecondStore->getDescription();

        $this->assertEquals('Test Category In Default Store', $categoryNameFromDefaultStore);
        $this->assertEquals('Test description in default store', $categoryDescriptionFromDefaultStore);
        $this->assertEquals('Test Category In Second Store', $categoryNameFromSecondStore);
        $this->assertEquals('Test description in second store', $categoryDescriptionFromSecondStore);
    }
}
