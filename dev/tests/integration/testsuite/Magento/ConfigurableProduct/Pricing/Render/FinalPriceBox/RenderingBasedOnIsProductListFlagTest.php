<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox;
use Magento\Framework\App\Area;
use Magento\Framework\Pricing\Render\Amount;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test price rendering according to is_product_list flag for Configurable product
 */
class RenderingBasedOnIsProductListFlagTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var FinalPrice
     */
    private $finalPrice;

    /**
     * @var RendererPool
     */
    private $rendererPool;

    /**
     * @var FinalPriceBox
     */
    private $finalPriceBox;

    protected function setUp(): void
    {
        /** @var  \Magento\Framework\App\Cache\StateInterface $cacheState */
        $cacheState = Bootstrap::getObjectManager()->get(\Magento\Framework\App\Cache\StateInterface::class);
        $cacheState->setEnabled(\Magento\Framework\App\Cache\Type\Collection::TYPE_IDENTIFIER, true);

        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->product = $productRepository->get('configurable');
        $this->finalPrice = Bootstrap::getObjectManager()->create(FinalPrice::class, [
            'saleableItem' => $this->product,
            'quantity' => null
        ]);
        $this->rendererPool = Bootstrap::getObjectManager()->create(RendererPool::class);
        $this->rendererPool->setData(
            [
                'default' =>
                    [
                        'default_amount_render_class' => Amount::class,
                        'default_amount_render_template' => 'Magento_Catalog::product/price/amount/default.phtml',
                    ],
            ]
        );
        $this->finalPriceBox = Bootstrap::getObjectManager()->create(FinalPriceBox::class, [
            'saleableItem' => $this->product,
            'price' => $this->finalPrice,
            'rendererPool' => $this->rendererPool
        ]);
        $this->finalPriceBox->setTemplate('Magento_ConfigurableProduct::product/price/final_price.phtml');

        /** @var Product $childProduct */
        $childProduct = $productRepository->get('simple_10', true);
        $childProduct->setData('special_price', 5.99);
        $productRepository->save($childProduct);
    }

    /**
     * Test when is_product_list flag is not specified. Regular and Special price should be rendered
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     */
    public function testRenderingByDefault()
    {
        $html = $this->finalPriceBox->toHtml();
        self::assertStringContainsString('5.99', $html);
        $this->assertGreaterThanOrEqual(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[contains(@class,"normal-price")]',
                $html
            )
        );
        $this->assertGreaterThanOrEqual(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[contains(@class,"old-price")]',
                $html
            )
        );
    }

    #[
        DataFixture('Magento/ConfigurableProduct/_files/product_configurable.php'),
        AppArea(Area::AREA_FRONTEND),
        DbIsolation(false),
    ]
    public function testHasSpecialPrice(): void
    {
        $productAttributeRepository = Bootstrap::getObjectManager()->get(ProductAttributeRepositoryInterface::class);
        $specialPrice = $productAttributeRepository->get('special_price');
        $specialPrice->setUsedInProductListing(false);
        $productAttributeRepository->save($specialPrice);

        try {
            self::assertTrue($this->finalPriceBox->hasSpecialPrice());
        } finally {
            $specialPrice->setUsedInProductListing(true);
            $productAttributeRepository->save($specialPrice);
        }
    }

    /**
     * Test when is_product_list flag is specified
     *
     * Special price should be valid
     * Regular price for Configurable product should be rendered for is_product_list = false (product page)
     *
     * @param bool $flag
     * @param int|bool $count
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoAppArea frontend
     * @dataProvider isProductListDataProvider
     * @magentoDbIsolation disabled
     */
    public function testRenderingAccordingToIsProductListFlag($flag, $count)
    {
        $this->finalPriceBox->setData('is_product_list', $flag);
        $html = $this->finalPriceBox->toHtml();

        self::assertStringContainsString('5.99', $html);
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[contains(@class,"normal-price")]',
                $html
            )
        );
        $this->assertEquals(
            $count,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[contains(@class,"old-price")]',
                $html
            )
        );
    }

    /**
     * @return array
     */
    public static function isProductListDataProvider()
    {
        return [
            'is_not_product_list' => [false, 1],
            'is_product_list' => [true, 0],
        ];
    }
}
