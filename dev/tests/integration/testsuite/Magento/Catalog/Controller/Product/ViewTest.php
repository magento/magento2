<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Product;

use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Http;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Eav\Model\GetAttributeSetByName;
use Magento\TestFramework\Request;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Logger\Monolog as MagentoMonologLogger;
use Magento\TestFramework\Response;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Integration test for product view front action.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewTest extends AbstractController
{
    /**
     * @var ProductRepositoryInterface $productRepository
     */
    private $productRepository;

    /**
     * @var AttributeSetRepositoryInterface $attributeSetRepository
     */
    private $attributeSetRepository;

    /**
     * @var ProductAttributeRepositoryInterface $attributeSetRepository
     */
    private $attributeRepository;

    /**
     * @var Type $productEntityType
     */
    private $productEntityType;

    /** @var Registry */
    private $registry;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var GetAttributeSetByName */
    private $getAttributeSetByName;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->_objectManager->create(ProductRepositoryInterface::class);
        $this->attributeSetRepository = $this->_objectManager->create(AttributeSetRepositoryInterface::class);
        $this->attributeRepository = $this->_objectManager->create(ProductAttributeRepositoryInterface::class);
        $this->productEntityType = $this->_objectManager->create(Type::class)
            ->loadByCode(Product::ENTITY);
        $this->registry = $this->_objectManager->get(Registry::class);
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
        $this->getAttributeSetByName = $this->_objectManager->get(GetAttributeSetByName::class);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/controllers/_files/products.php
     * @magentoConfigFixture current_store catalog/seo/product_canonical_tag 1
     * @return void
     */
    public function testViewActionWithCanonicalTag(): void
    {
        $this->markTestSkipped(
            'MAGETWO-40724: Canonical url from tests sometimes does not equal canonical url from action'
        );
        $this->dispatch('catalog/product/view/id/1/');

        $this->assertStringContainsString(
            '<link  rel="canonical" href="http://localhost/index.php/catalog/product/view/_ignore_category/1/id/1/" />',
            $this->getResponse()->getBody()
        );
    }

    /**
     * View product with custom attribute when attribute removed from it.
     *
     * It tests that after changing product attribute set from Default to Custom
     * there are no warning messages in log in case Custom not contains attribute from Default.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_country_of_manufacture.php
     * @magentoDataFixture Magento/Catalog/_files/attribute_set_based_on_default_without_country_of_manufacture.php
     * @return void
     */
    public function testViewActionCustomAttributeSetWithoutCountryOfManufacture(): void
    {
        /** @var MockObject|LoggerInterface $logger */
        $logger = $this->setupLoggerMock();
        $product = $this->productRepository->get('simple_with_com');
        $attributeSetCustom = $this->getAttributeSetByName->execute('custom_attribute_set_wout_com');
        $product->setAttributeSetId($attributeSetCustom->getAttributeSetId());
        $this->productRepository->save($product);

        /** @var ProductAttributeInterface $attributeCountryOfManufacture */
        $attributeCountryOfManufacture = $this->attributeRepository->get('country_of_manufacture');
        $logger->expects($this->never())
            ->method('warning')
            ->with(
                "Attempt to load value of nonexistent EAV attribute",
                [
                    'attribute_id' => $attributeCountryOfManufacture->getAttributeId(),
                    'entity_type' => ProductInterface::class,
                ]
            );

        $this->dispatch(sprintf('catalog/product/view/id/%s/', $product->getId()));
    }

    /**
     * @magentoDataFixture Magento/Quote/_files/is_not_salable_product.php
     * @return void
     */
    public function testDisabledProductInvisibility(): void
    {
        $product = $this->productRepository->get('simple-99');
        $this->dispatch(sprintf('catalog/product/view/id/%s/', $product->getId()));

        $this->assert404NotFound();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @dataProvider productVisibilityDataProvider
     * @param int $visibility
     * @return void
     */
    public function testProductVisibility(int $visibility): void
    {
        $product = $this->updateProductVisibility('simple2', $visibility);
        $this->dispatch(sprintf('catalog/product/view/id/%s/', $product->getId()));

        $this->assertProductIsVisible($product);
    }

    /**
     * @return array
     */
    public function productVisibilityDataProvider(): array
    {
        return [
            'catalog_search' => [Visibility::VISIBILITY_BOTH],
            'search' => [Visibility::VISIBILITY_IN_SEARCH],
            'catalog' => [Visibility::VISIBILITY_IN_CATALOG],
        ];
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/simple_products_not_visible_individually.php
     */
    public function testProductNotVisibleIndividually(): void
    {
        $product = $this->updateProductVisibility('simple_not_visible_1', Visibility::VISIBILITY_NOT_VISIBLE);
        $this->dispatch(sprintf('catalog/product/view/id/%s/', $product->getId()));

        $this->assert404NotFound();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_two_websites.php
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testProductVisibleOnTwoWebsites(): void
    {
        $currentStore = $this->storeManager->getStore();
        $product = $this->productRepository->get('simple-on-two-websites');
        $secondStoreId = $this->storeManager->getStore('fixture_second_store')->getId();
        $this->dispatch(sprintf('catalog/product/view/id/%s', $product->getId()));
        $this->assertProductIsVisible($product);
        $this->cleanUpCachedObjects();

        try {
            $this->storeManager->setCurrentStore($secondStoreId);
            $this->dispatch(sprintf('catalog/product/view/id/%s', $product->getId()));
            $this->assertProductIsVisible($product);
        } finally {
            $this->storeManager->setCurrentStore($currentStore);
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_two_websites.php
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testRemoveProductFromOneWebsiteVisibility(): void
    {
        $websiteId = $this->storeManager->getWebsite('test')->getId();
        $currentStore = $this->storeManager->getStore();
        $secondStoreId = $this->storeManager->getStore('fixture_second_store')->getId();
        $product = $this->updateProduct('simple-on-two-websites', ['website_ids' => [$websiteId]]);
        $this->dispatch(sprintf('catalog/product/view/id/%s', $product->getId()));
        $this->assert404NotFound();
        $this->cleanUpCachedObjects();

        try {
            $this->storeManager->setCurrentStore($secondStoreId);

            $this->dispatch(sprintf('catalog/product/view/id/%s', $product->getId()));
            $this->assertProductIsVisible($product);
        } finally {
            $this->storeManager->setCurrentStore($currentStore->getId());
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_two_websites.php
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testProductAttributeByStores(): void
    {
        $secondStoreId = $this->storeManager->getStore('fixture_second_store')->getId();
        $product = $this->productRepository->get('simple-on-two-websites');
        $currentStoreId = $this->storeManager->getStore()->getId();

        try {
            $this->storeManager->setCurrentStore($secondStoreId);
            $product = $this->updateProduct($product, ['status' => 2]);
            $this->dispatch(sprintf('catalog/product/view/id/%s', $product->getId()));
            $this->assert404NotFound();
            $this->cleanUpCachedObjects();
            $this->storeManager->setCurrentStore($currentStoreId);
            $this->dispatch(sprintf('catalog/product/view/id/%s', $product->getId()));
            $this->assertProductIsVisible($product);
        } finally {
            $this->storeManager->setCurrentStore($currentStoreId);
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @return void
     */
    public function testProductWithoutWebsite(): void
    {
        $product = $this->updateProduct('simple2', ['website_ids' => []]);
        $this->dispatch(sprintf('catalog/product/view/id/%s', $product->getId()));

        $this->assert404NotFound();
    }

    /**
     * Test that 404 page has product tag if product is not visible
     *
     * @magentoDataFixture Magento/Quote/_files/is_not_salable_product.php
     * @magentoCache full_page enabled
     * @return void
     */
    public function test404NotFoundPageCacheTags(): void
    {
        $cache = $this->_objectManager->get(Manager::class);
        $cache->clean(['full_page']);
        $product = $this->productRepository->get('simple-99');
        $this->dispatch(sprintf('catalog/product/view/id/%s/', $product->getId()));
        $this->assert404NotFound();
        $pTag = Product::CACHE_TAG . '_' . $product->getId();
        $hTags = $this->getResponse()->getHeader('X-Magento-Tags');
        $tags = $hTags && $hTags->getFieldValue() ? explode(',', $hTags->getFieldValue()) : [];
        $this->assertContains(
            $pTag,
            $tags,
            "Failed asserting that X-Magento-Tags: {$hTags->getFieldValue()} contains \"$pTag\""
        );
    }

    /**
     * @param string|ProductInterface $product
     * @param array $data
     * @return ProductInterface
     */
    public function updateProduct($product, array $data): ProductInterface
    {
        $product = is_string($product) ? $this->productRepository->get($product) : $product;
        $product->addData($data);

        return $this->productRepository->save($product);
    }

    /**
     * @inheritdoc
     */
    public function assert404NotFound()
    {
        parent::assert404NotFound();

        $this->assertNull($this->registry->registry('current_product'));
    }

    /**
     * Assert that product is available in storefront
     *
     * @param ProductInterface $product
     * @return void
     */
    private function assertProductIsVisible(ProductInterface $product): void
    {
        $this->assertEquals(
            Response::STATUS_CODE_200,
            $this->getResponse()->getHttpResponseCode(),
            'Wrong response code is returned'
        );
        $currentProduct = $this->registry->registry('current_product');
        $this->assertNotNull($currentProduct);
        $this->assertEquals(
            $product->getSku(),
            $currentProduct->getSku(),
            'Wrong product is registered'
        );
    }

    /**
     * Clean up cached objects.
     *
     * @return void
     */
    private function cleanUpCachedObjects(): void
    {
        $this->_objectManager->removeSharedInstance(Http::class);
        $this->_objectManager->removeSharedInstance(Request::class);
        $this->_objectManager->removeSharedInstance(Response::class);
        $this->_request = null;
        $this->_response = null;
    }

    /**
     * Setup logger mock to check there are no warning messages logged.
     *
     * @return MockObject
     */
    private function setupLoggerMock(): MockObject
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->_objectManager->addSharedInstance($logger, MagentoMonologLogger::class);

        return $logger;
    }

    /**
     * Update product visibility
     *
     * @param string $sku
     * @param int $visibility
     * @return ProductInterface
     */
    private function updateProductVisibility(string $sku, int $visibility): ProductInterface
    {
        $product = $this->productRepository->get($sku);
        $product->setVisibility($visibility);

        return $this->productRepository->save($product);
    }
}
