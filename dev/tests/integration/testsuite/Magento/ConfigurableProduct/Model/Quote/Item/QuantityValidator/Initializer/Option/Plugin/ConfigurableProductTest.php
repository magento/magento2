<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Quote\Item\QuantityValidator\Initializer\Option\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as ConfigurableAttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Framework\DataObject;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Stock validation of configurable product quote items
 */
class ConfigurableProductTest extends TestCase
{
    /**
     * The backordered quantity of the selected variant must be kept on the configurable quote item
     */
    #[
        DataFixture(ConfigurableAttributeFixture::class, as: 'attribute'),
        DataFixture(
            ProductFixture::class,
            [
                'extension_attributes' => [
                    'stock_item' => [
                        'qty' => 1,
                        'is_in_stock' => true,
                        'use_config_backorders' => false,
                        'backorders' => 1,
                    ],
                ],
            ],
            'variant'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attribute$'], '_links' => ['$variant$']],
            'configurable'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
    ]
    public function testConfigurableQuoteItemKeepsBackordersOfVariant(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $fixtures = DataFixtureStorageManager::getStorage();
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $attribute = $fixtures->get('attribute');
        $variant = $productRepository->get($fixtures->get('variant')->getSku(), false, null, true);
        $configurable = $productRepository->get($fixtures->get('configurable')->getSku(), false, null, true);
        $cart = $objectManager->get(CartRepositoryInterface::class)->get($fixtures->get('cart')->getId());

        $quoteItem = $cart->addProduct(
            $configurable,
            new DataObject(
                [
                    'qty' => 3,
                    'super_attribute' => [
                        $attribute->getAttributeId() => $variant->getData($attribute->getAttributeCode()),
                    ],
                ]
            )
        );

        $this->assertEquals(2, $quoteItem->getBackorders());
    }
}
