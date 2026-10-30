<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Plugin\Model;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox;
use Magento\Framework\Pricing\Render\Amount;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for SpecialPrice pricing class
 */
#[
    AppArea('frontend'),
    DbIsolation(true)
]
class ConfigPluginTest extends TestCase
{
    /**
     * This tests the fix for the issue where special_price doesn't display when
     * the attribute has "Used in Product Listing = No" setting
     *
     * @return void
     */
    #[
        DataFixture(
            ProductFixture::class,
            ['sku' => 'simple-special-price', 'price' => 100, 'special_price' => 90]
        ),
    ]
    public function testGetSpecialPriceInPLPageUsedInProdListingFalse(): void
    {
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple-special-price');
        $finalPrice = Bootstrap::getObjectManager()->create(FinalPrice::class, [
            'saleableItem' => $product,
            'quantity' => null
        ]);
        $rendererPool = Bootstrap::getObjectManager()->create(RendererPool::class);
        $rendererPool->setData(
            [
                'default' =>
                    [
                        'default_amount_render_class' => Amount::class,
                        'default_amount_render_template' => 'Magento_Catalog::product/price/amount/default.phtml',
                    ],
            ]
        );
        $finalPriceBox = Bootstrap::getObjectManager()->create(FinalPriceBox::class, [
            'saleableItem' => $product,
            'price' => $finalPrice,
            'rendererPool' => $rendererPool
        ]);
        $finalPriceBox->setTemplate('Magento_Catalog::product/price/final_price.phtml');

        $productAttributeRepository = Bootstrap::getObjectManager()->get(ProductAttributeRepositoryInterface::class);
        $specialPrice = $productAttributeRepository->get('special_price');
        $specialPrice->setUsedInProductListing(false);
        $productAttributeRepository->save($specialPrice);

        $specialPriceHtml = $finalPriceBox->renderAmount(
            $finalPriceBox->getPriceType('final_price')->getAmount()
        );
        $this->assertStringContainsString('90.00', $specialPriceHtml);

        $specialPrice->setUsedInProductListing(true);
        $productAttributeRepository->save($specialPrice);
    }
}
