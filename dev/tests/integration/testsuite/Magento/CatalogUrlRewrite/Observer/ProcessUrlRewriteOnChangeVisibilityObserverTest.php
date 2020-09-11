<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
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
     * Set up
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->eventManager = $this->objectManager->create(ManagerInterface::class);
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
        $storeManager->setCurrentStore(0);

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

        $this->eventManager->dispatch(
            'catalog_product_attribute_update_before',
            [
                'attributes_data' => [ ProductInterface::VISIBILITY => Visibility::VISIBILITY_NOT_VISIBLE ],
                'product_ids' => [$product->getId()]
            ]
        );

        $actual = $this->getActualResults($productFilter);
        $this->assertCount(0, $actual);
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
