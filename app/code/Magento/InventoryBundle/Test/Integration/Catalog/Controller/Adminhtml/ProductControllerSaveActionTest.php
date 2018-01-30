<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundle\Test\Integration\Catalog\Controller\Adminhtml;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 */
class ProductControllerSaveActionTest extends AbstractBackendController
{
    const BUNDLE_PRODUCT_SKU = 'SKU-1-test-product-bundle';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    protected function setUp()
    {
        parent::setUp();
        $this->productRepository = $this->_objectManager->create(ProductRepositoryInterface::class);
        $this->sourceItemRepository = $this->_objectManager->create(SourceItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = $this->_objectManager->create(SearchCriteriaBuilder::class);
    }

    protected function tearDown()
    {
        $this->productRepository->deleteById(self::BUNDLE_PRODUCT_SKU);
        parent::tearDown();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     */
    public function testBundleProductShouldBeInStockOnCreateButShouldNotProcessSourceItems()
    {
        $this->markTestSkipped('https://github.com/magento-engcom/msi/issues/370');
        $this->getRequest()->setPostValue($this->prepareFormData());

        $this->dispatch('backend/catalog/product/save');

        $this->assertSessionMessages(
            $this->contains('You saved the product.'),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        /** @var ProductInterface  $product */
        $product = $this->productRepository->get(self::BUNDLE_PRODUCT_SKU);
        self::assertEquals(1, $product->getExtensionAttributes()->getStockItem()->getIsInStock());
        /** @var SearchCriteria $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('sku', self::BUNDLE_PRODUCT_SKU)->create();
        $sourceItemsCount = $this->sourceItemRepository->getList($searchCriteria)->getTotalCount();
        self::assertEquals(0, $sourceItemsCount);
    }

    /**
     * @return array
     */
    private function prepareFormData(): array
    {
        $product = [
            "sku" => self::BUNDLE_PRODUCT_SKU,
            "name" => 'bundle product',
            "price" => 50,
            'attribute_set_id' => 4,
            'type_id' => Type::TYPE_BUNDLE
        ];
        /** @var ProductInterface $simpleProduct */
        $simpleProduct = $this->productRepository->get('SKU-1');
        $bundleProductOptions = [
            [
                "bundle_options" => [
                    [
                        0 => [
                            'title' => 'Bundle Product Items',
                            'default_title' => 'Bundle Product Items',
                            'type' => 'select',
                            'required' => 1,
                            'delete' => '',
                            'bundle_selections' => [
                                0 => [
                                    'product_id' => $simpleProduct->getId(),
                                    'selection_qty' => 1,
                                    'selection_can_change_qty' => 1,
                                    'delete' => '',
                                ]
                            ]
                        ]
                    ],
                ],
            ],
        ];
        $formData = [
            'product' => $product,
            'back' => 'new',
            'affect_bundle_product_selections' => 1,
            'bundle_options' => $bundleProductOptions
        ];

        return $formData;
    }
}
