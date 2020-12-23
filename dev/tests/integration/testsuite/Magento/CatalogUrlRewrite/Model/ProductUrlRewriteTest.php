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
use Magento\CatalogUrlRewrite\Model\Map\DataProductUrlRewriteDatabaseMap;
use Magento\Store\Model\ScopeInterface;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Model\OptionProvider;

/**
 * Class for product url rewrites tests
 *
 * @magentoDbIsolation enabled
 * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
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
    public function productDataProvider(): array
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
    public function productEditProvider(): array
    {
        return [
            [
                'expected_data' => [
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
    public function existingUrlKeyProvider(): array
    {
        return [
            [
                'with_specified_existing_product_url_key' => [
                    'type_id' => Type::TYPE_SIMPLE,
                    'attribute_set_id' => 4,
                    'sku' => 'test-simple-product',
                    'name' => 'test-simple-product',
                    'price' => 150,
                    'url_key' => 'simple-product',
                    'store_ids' => [1]
                ],
                'with_autogenerated_existing_product_url_key' => [
                    'type_id' => Type::TYPE_SIMPLE,
                    'attribute_set_id' => 4,
                    'sku' => 'test-simple-product',
                    'name' => 'simple product',
                    'price' => 150,
                    'store_ids' => [1]
                ],
                'with_specified_existing_category_url_key' => [
                    'type_id' => Type::TYPE_SIMPLE,
                    'attribute_set_id' => 4,
                    'sku' => 'test-simple-product',
                    'name' => 'test-simple-product',
                    'price' => 150,
                    'url_key' => 'category-1',
                    'store_ids' => [1]
                ],
                'with_autogenerated_existing_category_url_key' => [
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
