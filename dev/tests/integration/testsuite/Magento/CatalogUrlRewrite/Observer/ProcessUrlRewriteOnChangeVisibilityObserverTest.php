<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessUrlRewriteOnChangeVisibilityObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->eventManager = $this->objectManager->create(ManagerInterface::class);

        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/product_rewrite_multistore.php
     * @magentoAppIsolation enabled
     */
    public function testMakeProductInvisibleViaMassAction()
    {
        /** @var \Magento\Catalog\Model\Product $product*/
        $product = $this->productRepository->get('product1');

        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $firstStore = current($product->getStoreIds());
        $testStore = $storeManager->getStore('test');
        $productFilter = [
            UrlRewrite::ENTITY_TYPE => 'product',
        ];

        $expected = [
            [
                'request_path' => "product-1.html",
                'target_path' => "catalog/product/view/id/" . $product->getId(),
                'is_auto_generated' => 1,
                'redirect_type' => 0,
                'store_id' => $firstStore,
            ],
            [
                'request_path' => "product-1.html",
                'target_path' => "catalog/product/view/id/" . $product->getId(),
                'is_auto_generated' => 1,
                'redirect_type' => 0,
                'store_id' => $testStore->getId(),
            ]
        ];

        $actual = $this->getActualResults($productFilter);
        foreach ($expected as $row) {
            $this->assertContains($row, $actual);
        }

        $this->eventManager->dispatch(
            'catalog_product_attribute_update_before',
            [
                'attributes_data' => [ ProductInterface::VISIBILITY => Visibility::VISIBILITY_NOT_VISIBLE ],
                'product_ids' => [$product->getId()],
                'store_id' => $firstStore,
            ]
        );

        $actual = $this->getActualResults($productFilter);
        //Initially count was 2, when visibility of 1 store view is set to not visible individually, the new count is 1
        $this->assertCount(1, $actual);
    }

    /**
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/product_invisible_multistore.php
     * @magentoAppIsolation enabled
     */
    public function testMakeProductVisibleViaMassAction()
    {
        /** @var \Magento\Catalog\Model\Product $product*/
        $product = $this->productRepository->get('product1');

        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $storeManager->setCurrentStore(0);

        $testStore = $storeManager->getStore('test');
        $productFilter = [
            UrlRewrite::ENTITY_TYPE => 'product',
        ];

        $actual = $this->getActualResults($productFilter);
        $this->assertCount(0, $actual);

        $this->eventManager->dispatch(
            'catalog_product_attribute_update_before',
            [
                'attributes_data' => [ ProductInterface::VISIBILITY => Visibility::VISIBILITY_BOTH ],
                'product_ids' => [$product->getId()]
            ]
        );

        $expected = [
            [
                'request_path' => "product-1.html",
                'target_path' => "catalog/product/view/id/" . $product->getId(),
                'is_auto_generated' => 1,
                'redirect_type' => 0,
                'store_id' => '1',
            ],
            [
                'request_path' => "product-1.html",
                'target_path' => "catalog/product/view/id/" . $product->getId(),
                'is_auto_generated' => 1,
                'redirect_type' => 0,
                'store_id' => $testStore->getId(),
            ]
        ];

        $actual = $this->getActualResults($productFilter);
        foreach ($expected as $row) {
            $this->assertContains($row, $actual);
        }
    }

    /**
     * Test for multistore properties of the product to be respected in generated UrlRewrites
     * during the mass update for visibility change
     *
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     */
    #[
        AppIsolation(true),
        DataFixture(WebsiteFixture::class, as: 'w1'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$w1.id$'], 'g1'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$g1.id$'], 's1'),
        DataFixture(CategoryFixture::class, as: 'c1'),
        DataFixture(
            ProductFixture::class,
            ['category_ids' => ['$c1.id$'], 'visibility' => 1, 'website_ids' => [1, '$w1.id$']],
            'p1'
        ),
    ]
    public function testMassActionUrlRewriteForStore()
    {
        $product = $this->fixtures->get('p1');
        $category = $this->fixtures->get('c1');
        $store = $this->fixtures->get('s1');

        $productFilter = [
            UrlRewrite::ENTITY_TYPE => 'product',
        ];

        $beforeUpdate = $this->getActualResults($productFilter);
        $this->assertCount(0, $beforeUpdate);

        $this->eventManager->dispatch(
            'catalog_product_attribute_update_before',
            [
                'attributes_data' => [ ProductInterface::VISIBILITY => Visibility::VISIBILITY_BOTH ],
                'product_ids' => [$product->getId()],
                'store_id' => Store::DEFAULT_STORE_ID
            ]
        );

        $expected = [
            [
                'request_path' => $product->getUrlKey() . ".html",
                'target_path' => "catalog/product/view/id/" . $product->getId(),
                'is_auto_generated' => 1,
                'redirect_type' => 0,
                'store_id' => (String) Store::DISTRO_STORE_ID
            ],
            [
                'request_path' => $category->getUrlKey() . '/' . $product->getUrlKey() . ".html",
                'target_path' => "catalog/product/view/id/" . $product->getId() . '/category/' . $category->getId(),
                'is_auto_generated' => 1,
                'redirect_type' => 0,
                'store_id' => (String) Store::DISTRO_STORE_ID
            ],
            [
                'request_path' => $product->getUrlKey() . ".html",
                'target_path' => "catalog/product/view/id/" . $product->getId(),
                'is_auto_generated' => 1,
                'redirect_type' => 0,
                'store_id' => (String) $store->getId()
            ],
            [
                'request_path' => $category->getUrlKey() . '/' . $product->getUrlKey() . ".html",
                'target_path' => "catalog/product/view/id/" . $product->getId() . '/category/' . $category->getId(),
                'is_auto_generated' => 1,
                'redirect_type' => 0,
                'store_id' => (String) $store->getId()
            ],
        ];

        $actual = $this->getActualResults($productFilter);
        foreach ($expected as $row) {
            $this->assertContains($row, $actual);
        }
    }

    /**
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/products_invisible.php
     * @magentoAppIsolation enabled
     */
    public function testErrorOnDuplicatedUrlKey()
    {
        $skus = ['product1', 'product2'];
        foreach ($skus as $sku) {
            /** @var \Magento\Catalog\Model\Product $product */
            $productIds[] = $this->productRepository->get($sku)->getId();
        }
        $this->expectException(UrlAlreadyExistsException::class);
        $this->expectExceptionMessage('Can not change the visibility of the product with SKU equals "product2". '
            . 'URL key "product-1" for specified store already exists.');

        $this->eventManager->dispatch(
            'catalog_product_attribute_update_before',
            [
                'attributes_data' => [ ProductInterface::VISIBILITY => Visibility::VISIBILITY_BOTH ],
                'product_ids' => $productIds
            ]
        );
    }

    /**
     * @param array $filter
     * @return array
     */
    private function getActualResults(array $filter)
    {
        /** @var \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(\Magento\UrlRewrite\Model\UrlFinderInterface::class);
        $actualResults = [];
        foreach ($urlFinder->findAllByData($filter) as $url) {
            $actualResults[] = [
                'request_path' => $url->getRequestPath(),
                'target_path' => $url->getTargetPath(),
                'is_auto_generated' => (int)$url->getIsAutogenerated(),
                'redirect_type' => $url->getRedirectType(),
                'store_id' => $url->getStoreId()
            ];
        }
        return $actualResults;
    }
}
