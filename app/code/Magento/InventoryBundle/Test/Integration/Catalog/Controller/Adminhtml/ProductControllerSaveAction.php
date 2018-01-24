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
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Bundle\Api\Data\LinkInterface;

/**
 * @magentoAppArea adminhtml
 */
class ProductControllerSaveAction extends AbstractBackendController
{
    const BUNDLE_PRODUCT_SKU = 'SKU-1-test-product-bundle';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        parent::setUp();
        $this->productRepository = $this->_objectManager->create(ProductRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     */
    public function testIsBundleProductWithSimpleProductInStockAfterCreate()
    {
        $bundleProductOptions = [
            [
                "title" => "Test simple",
                "type" => "select",
                "required" => false,
                "product_links" => [
                    [
                        "sku" => 'SKU-1',
                        "qty" => 1.5,
                        'is_default' => false,
                        'price' => 1.0,
                        'price_type' => LinkInterface::PRICE_TYPE_FIXED,
                    ],
                ],
            ],
        ];
        $product = [
            "sku" => self::BUNDLE_PRODUCT_SKU,
            "name" => 'bundle product',
            "type_id" => Type::TYPE_BUNDLE,
            "price" => 50,
            'attribute_set_id' => 4,
            "custom_attributes" => [
                "price_type" => [
                    'attribute_code' => 'price_type',
                    'value' => \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED
                ],
                "price_view" => [
                    "attribute_code" => "price_view",
                    "value" => "1",
                ],
            ],
            "extension_attributes" => [
                "bundle_product_options" => $bundleProductOptions,
            ],
        ];
        $this->getRequest()->setPostValue(['product' => $product, 'back' => 'new']);

        $this->dispatch('backend/catalog/product/save');

        $this->assertSessionMessages(
            $this->contains('You saved the product.'),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        /** @var ProductInterface  $product */
        $product = $this->productRepository->get(self::BUNDLE_PRODUCT_SKU);
        self::assertEquals(1, $product->getExtensionAttributes()->getStockItem()->getIsInStock());
    }
}
