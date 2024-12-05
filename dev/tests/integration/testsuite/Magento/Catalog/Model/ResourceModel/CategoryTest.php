<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\Indexer\Category\Product as CategoryProductIndexer;
use Magento\Catalog\Model\Indexer\Product\Category as ProductCategoryIndexer;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Indexer\Cron\UpdateMview;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests category resource model
 *
 * @see \Magento\Catalog\Model\ResourceModel\Category
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryTest extends TestCase
{
    private const BASE_TMP_PATH = 'catalog/tmp/category';

    private const BASE_PATH = 'catalog/category';

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var CategoryResource */
    private $categoryResource;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var CategoryCollection */
    private $categoryCollection;

    /** @var Filesystem */
    private $filesystem;

    /** @var WriteInterface */
    private $mediaDirectory;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $this->categoryResource = $this->objectManager->get(CategoryResource::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->categoryCollection = $this->objectManager->get(CategoryCollectionFactory::class)->create();
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->productResource = Bootstrap::getObjectManager()->get(ProductResource::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->mediaDirectory->delete(self::BASE_PATH);

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoDataFixture Magento/Catalog/_files/catalog_tmp_category_image.php
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testAddImageForCategory(): void
    {
        $dataImage = [
            'name' => 'magento_small_image.jpg',
            'type' => 'image/jpg',
            'tmp_name' => '/tmp/phpDstnAx',
            'file' => 'magento_small_image.jpg',
            'url' => $this->prepareDataImageUrl('magento_small_image.jpg'),
        ];
        $imageRelativePath = self::BASE_PATH . DIRECTORY_SEPARATOR . $dataImage['file'];
        $expectedImage = DIRECTORY_SEPARATOR . $this->storeManager->getStore()->getBaseMediaDir()
            . DIRECTORY_SEPARATOR . $imageRelativePath;
        /** @var CategoryModel $category */
        $category = $this->categoryRepository->get(333);
        $category->setImage([$dataImage]);

        $this->categoryResource->save($category);

        $categoryModel = $this->categoryCollection
            ->addAttributeToSelect('image')
            ->addIdFilter([$category->getId()])
            ->getFirstItem();
        $this->assertEquals(
            $expectedImage,
            $categoryModel->getImage(),
            'The path of the expected image does not match the path to the actual image.'
        );
        $this->assertTrue($this->mediaDirectory->isExist($imageRelativePath));
    }

    /**
     * Test that adding or removing products in a category should not trigger full reindex in scheduled update mode
     *
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/category_with_three_products.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @magentoDataFixture Magento/Catalog/_files/catalog_category_product_reindex_all.php
     * @magentoDataFixture Magento/Catalog/_files/catalog_product_category_reindex_all.php
     * @magentoDataFixture Magento/Catalog/_files/enable_catalog_product_reindex_schedule.php
     * @dataProvider catalogProductChangesWithScheduledUpdateDataProvider
     * @param array $products
     * @return void
     */
    public function testCatalogProductChangesWithScheduledUpdate(array $products): void
    {
        // products are ordered by entity_id DESC because their positions are same and equal to 0
        $initialProducts = ['simple1002', 'simple1001', 'simple1000'];
        $defaultStoreId = (int) $this->storeManager->getDefaultStoreView()->getId();
        $category = $this->getCategory(['name' => 'Category 999']);
        $expectedProducts = array_keys($products);
        $productIdsBySkus = $this->productResource->getProductsIdsBySkus($expectedProducts);
        $postedProducts = [];
        foreach ($products as $sku => $position) {
            $postedProducts[$productIdsBySkus[$sku]] = $position;
        }
        $category->setPostedProducts($postedProducts);
        $this->categoryResource->save($category);
        // Indices should not be invalidated when adding/removing/reordering products in a category.
        $categoryProductIndexer = $this->getIndexer(CategoryProductIndexer::INDEXER_ID);
        $this->assertTrue(
            $categoryProductIndexer->isValid(),
            '"Indexed category/products association" indexer should not be invalidated.'
        );
        $productCategoryIndexer = $this->getIndexer(ProductCategoryIndexer::INDEXER_ID);
        $this->assertTrue(
            $productCategoryIndexer->isValid(),
            '"Indexed product/categories association" indexer should not be invalidated.'
        );
        // catalog products is not update until partial reindex occurs
        $collection = $this->getCategoryProducts($category, $defaultStoreId);
        $this->assertEquals($initialProducts, $collection->getColumnValues('sku'));
        // Execute MVIEW cron handler for cron job "indexer_update_all_views"
        /** @var $mViewCron UpdateMview */
        $mViewCron = $this->objectManager->create(UpdateMview::class);
        $mViewCron->execute();
        $collection = $this->getCategoryProducts($category, $defaultStoreId);
        $this->assertEquals($expectedProducts, $collection->getColumnValues('sku'));
    }

    /**
     * @return array
     */
    public static function catalogProductChangesWithScheduledUpdateDataProvider(): array
    {
        return [
            'change products position' => [
                [
                    'simple1002' => 1,
                    'simple1000' => 2,
                    'simple1001' => 3,
                ]
            ],
            'Add new product' => [
                [
                    'simple1002' => 1,
                    'simple1000' => 2,
                    'simple-1' => 3,
                    'simple1001' => 4,
                ]
            ],
            'Delete product' => [
                [
                    'simple1002' => 1,
                    'simple1000' => 2,
                ]
            ]
        ];
    }

    /**
     * @param CategoryModel $category
     * @param int $defaultStoreId
     * @return ProductCollection
     */
    private function getCategoryProducts(CategoryModel $category, int $defaultStoreId)
    {
        /** @var ProductCollection $collection */
        $collection = $this->objectManager->create(ProductCollection::class);
        $collection->setStoreId($defaultStoreId);
        $collection->addCategoryFilter($category);
        $collection->addAttributeToSort('position');
        return $collection;
    }

    /**
     * @param array $filters
     * @return CategoryModel
     */
    private function getCategory(array $filters): CategoryModel
    {
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->objectManager->create(CategoryCollection::class);
        foreach ($filters as $field => $value) {
            $categoryCollection->addFieldToFilter($field, $value);
        }

        return $categoryCollection->getFirstItem();
    }

    /**
     * @param string $indexerId
     * @return IndexerInterface
     */
    private function getIndexer(string $indexerId): IndexerInterface
    {
        /** @var IndexerRegistry $indexerRegistry */
        $indexerRegistry = $this->objectManager->get(IndexerRegistry::class);
        return $indexerRegistry->get($indexerId);
    }

    /**
     * Prepare image url for image data
     *
     * @param string $file
     * @return string
     */
    private function prepareDataImageUrl(string $file): string
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . self::BASE_TMP_PATH . DIRECTORY_SEPARATOR . $file;
    }
}
