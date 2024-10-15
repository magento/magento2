<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogUrlRewrite\Model\Map\DataProductUrlRewriteDatabaseMap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Source\Csv;
use Magento\Store\Model\ScopeInterface;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Model\OptionProvider;
use Psr\Log\LoggerInterface;

/**
 * Class for product url rewrites tests
 *
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductUrlRewriteTest extends AbstractUrlRewriteTest
{
    /** @var ProductFactory */
    private $productFactory;

    /** @var string */
    private $suffix;

    /** @var ProductResource */
    private $productResource;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->productResource = $this->objectManager->create(ProductResource::class);
        $this->productFactory = $this->objectManager->get(ProductFactory::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->suffix = $this->config->getValue(
            ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @dataProvider productDataProvider
     * @param array $data
     * @return void
     */
    public function testUrlRewriteOnProductSave(array $data): void
    {
        $product = $this->saveProduct($data['data']);
        $this->assertNotNull($product->getId(), 'The product was not created');
        $productUrlRewriteCollection = $this->getEntityRewriteCollection($product->getId());
        $this->assertRewrites(
            $productUrlRewriteCollection,
            $this->prepareData($data['expected_data'], (int)$product->getId())
        );
    }

    /**
     * @return array
     */
    public static function productDataProvider(): array
    {
        return [
            'without_url_key' => [
                [
                    'data' => [
                        'type_id' => Type::TYPE_SIMPLE,
                        'visibility' => Visibility::VISIBILITY_BOTH,
                        'attribute_set_id' => 4,
                        'sku' => 'test-product',
                        'name' => 'test product',
                        'price' => 150,
                        'website_ids' => [1]
                    ],
                    'expected_data' => [
                        [
                            'request_path' => 'test-product%suffix%',
                            'target_path' => 'catalog/product/view/id/%id%',
                        ],
                    ],
                ],
            ],
            'with_url_key' => [
                [
                    'data' => [
                        'type_id' => Type::TYPE_SIMPLE,
                        'attribute_set_id' => 4,
                        'sku' => 'test-product',
                        'visibility' => Visibility::VISIBILITY_BOTH,
                        'name' => 'test product',
                        'price' => 150,
                        'url_key' => 'test-product-url-key',
                        'website_ids' => [1]
                    ],
                    'expected_data' => [
                        [
                            'request_path' => 'test-product-url-key%suffix%',
                            'target_path' => 'catalog/product/view/id/%id%',
                        ],
                    ],
                ],
            ],
            'with_invisible_product' => [
                [
                    'data' => [
                        'type_id' => Type::TYPE_SIMPLE,
                        'attribute_set_id' => 4,
                        'sku' => 'test-product',
                        'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
                        'name' => 'test product',
                        'price' => 150,
                        'url_key' => 'test-product-url-key',
                        'website_ids' => [1]
                    ],
                    'expected_data' => [],
                ],
            ],
        ];
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/product_simple.php
     * @dataProvider productEditProvider
     * @param array $expectedData
     * @return void
     */
    public function testUrlRewriteOnProductEdit(array $expectedData): void
    {
        $product = $this->productRepository->get('simple');
        $data = [
            'url_key' => 'new-url-key',
            'url_key_create_redirect' => $product->getUrlKey(),
            'save_rewrites_history' => true,
        ];
        $product = $this->saveProduct($data, $product);
        $productRewriteCollection = $this->getEntityRewriteCollection($product->getId());
        $this->assertRewrites(
            $productRewriteCollection,
            $this->prepareData($expectedData, (int)$product->getId())
        );
    }

    /**
     * @return array
     */
    public static function productEditProvider(): array
    {
        return [
            [
                'expectedData' => [
                    [
                        'request_path' => 'new-url-key%suffix%',
                        'target_path' => 'catalog/product/view/id/%id%',
                    ],
                    [
                        'request_path' => 'simple-product%suffix%',
                        'target_path' => 'new-url-key%suffix%',
                        'redirect_type' => OptionProvider::PERMANENT,
                    ],
                ],
            ],
        ];
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/category_with_products.php
     * @dataProvider existingUrlKeyProvider
     * @param array $data
     * @return void
     */
    public function testUrlRewriteOnProductSaveWithExistingUrlKey(array $data): void
    {
        $this->expectException(UrlAlreadyExistsException::class);
        $this->expectExceptionMessage((string)__('URL key for specified store already exists.'));
        $this->saveProduct($data);
    }

    /**
     * @return array
     */
    public static function existingUrlKeyProvider(): array
    {
        return [
            [
                [
                    'type_id' => Type::TYPE_SIMPLE,
                    'attribute_set_id' => 4,
                    'sku' => 'test-simple-product',
                    'name' => 'test-simple-product',
                    'price' => 150,
                    'url_key' => 'simple-product',
                    'store_ids' => [1]
                ],
                [
                    'type_id' => Type::TYPE_SIMPLE,
                    'attribute_set_id' => 4,
                    'sku' => 'test-simple-product',
                    'name' => 'simple product',
                    'price' => 150,
                    'store_ids' => [1]
                ],
                [
                    'type_id' => Type::TYPE_SIMPLE,
                    'attribute_set_id' => 4,
                    'sku' => 'test-simple-product',
                    'name' => 'test-simple-product',
                    'price' => 150,
                    'url_key' => 'category-1',
                    'store_ids' => [1]
                ],
                [
                    'type_id' => Type::TYPE_SIMPLE,
                    'attribute_set_id' => 4,
                    'sku' => 'test-simple-product',
                    'name' => 'category 1',
                    'price' => 150,
                    'store_ids' => [1]
                ],
            ],
        ];
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     */
    public function testUrlRewritesAfterProductDelete(): void
    {
        $product = $this->productRepository->get('simple2');
        $rewriteIds = $this->getEntityRewriteCollection($product->getId())->getAllIds();
        $this->productRepository->delete($product);
        $this->assertEmpty(
            array_intersect($this->getAllRewriteIds(), $rewriteIds),
            'Not all expected category url rewrites were deleted'
        );
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @return void
     */
    public function testProductUrlRewritePerStoreViews(): void
    {
        $urlKeySecondStore = 'url-key-for-second-store';
        $secondStoreId = $this->storeRepository->get('fixture_second_store')->getId();
        $product = $this->productRepository->get('simple2');
        $urlKeyFirstStore = $product->getUrlKey();
        $product = $this->saveProduct(
            ['store_id' => $secondStoreId, 'url_key' => $urlKeySecondStore],
            $product
        );
        $urlRewriteItems = $this->getEntityRewriteCollection($product->getId())->getItems();
        $this->assertTrue(count($urlRewriteItems) == 2);
        foreach ($urlRewriteItems as $item) {
            $item->getData('store_id') == $secondStoreId
                ? $this->assertEquals($urlKeySecondStore . $this->suffix, $item->getRequestPath())
                : $this->assertEquals($urlKeyFirstStore . $this->suffix, $item->getRequestPath());
        }
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * Check if redirects are generated correctly while product urls are changed during import process.
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testImportProductRewrites()
    {
        $data = [
            ['sku' => 'simple1', 'request_path' => 'simple-product1', 'target_path' => 'product-1-updated'],
            ['sku' => 'simple2', 'request_path' => 'simple-product2', 'target_path' => 'product-2-updated'],
            ['sku' => 'simple3', 'request_path' => 'simple-product3', 'target_path' => 'product-3-updated'],
        ];

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productImport = $this->objectManager->create(
            Product::class,
            ['logger' => $logger]
        );
        $filesystem = $this->objectManager->get(Filesystem::class);

        foreach ($data as $datum) {
            $this->assertEquals(
                $datum['request_path'],
                $this->productRepository->get($datum['sku'], false, null, true)->getUrlKey()
            );
        }

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import_with_rewrites.csv',
                'directory' => $directory
            ]
        );
        $errors = $productImport->setParameters(
            ['behavior' => Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();
        $this->assertTrue($errors->getErrorsCount() === 0);
        $productImport->importData();

        foreach ($data as $datum) {
            $product = $this->productRepository->get($datum['sku'], false, null, true);
            $this->assertEquals(
                $datum['target_path'],
                $product->getUrlKey()
            );

            $productUrlRewriteCollection = $this->getEntityRewriteCollection($product->getId());
            $rewriteExists = false;
            foreach ($productUrlRewriteCollection as $item) {
                if ($item->getTargetPath() === $datum['target_path'] . $this->suffix &&
                    $item->getRequestPath() === $datum['request_path'] . $this->suffix) {
                    $rewriteExists = true;
                    break;
                }

            }

            $this->assertTrue($rewriteExists);
        }
    }

    /**
     * Save product with data using resource model directly
     *
     * @param array $data
     * @param ProductInterface|null $product
     * @return ProductInterface
     */
    protected function saveProduct(array $data, $product = null): ProductInterface
    {
        $product = $product ?: $this->productFactory->create();
        $product->addData($data);
        $this->productResource->save($product);

        return $product;
    }

    /**
     * @inheritdoc
     */
    protected function getUrlSuffix(): string
    {
        return $this->suffix;
    }

    /**
     * @inheritdoc
     */
    protected function getEntityType(): string
    {
        return DataProductUrlRewriteDatabaseMap::ENTITY_TYPE;
    }
}
