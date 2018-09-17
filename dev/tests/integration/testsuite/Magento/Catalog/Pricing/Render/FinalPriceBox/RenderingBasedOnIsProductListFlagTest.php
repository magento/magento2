<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Pricing\Render\FinalPriceBox;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Render\FinalPriceBox;
use Magento\Framework\Pricing\Render\Amount;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test price rendering according to is_product_list flag
 */
class RenderingBasedOnIsProductListFlagTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->product = $productRepository->get('simple');
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
            'rendererPool' => $this->rendererPool,
        ]);
        $this->finalPriceBox->setTemplate('Magento_Catalog::product/price/final_price.phtml');
    }

    /**
     * Test when is_product_list flag is not specified. Regular and Special price should be rendered
     *
     * @magentoDataFixture Magento/Catalog/_files/product_special_price.php
     * @magentoAppArea frontend
     */
    public function testRenderingByDefault()
    {
        $html = $this->finalPriceBox->toHtml();
        self::assertContains('5.99', $html);
        self::assertSelectCount('.special-price', true, $html);
        self::assertSelectCount('.old-price', true, $html);
    }

    /**
     * Test when is_product_list flag is specified. Regular and Special price should be rendered with any flag value
     * For example should be rendered for product page and for list of products
     *
     * @param bool $flag
     * @magentoDataFixture Magento/Catalog/_files/product_special_price.php
     * @magentoAppArea frontend
     * @dataProvider isProductListDataProvider
     */
    public function testRenderingAccordingToIsProductListFlag($flag)
    {
        $this->finalPriceBox->setData('is_product_list', $flag);
        $html = $this->finalPriceBox->toHtml();
        self::assertContains('5.99', $html);
        self::assertSelectCount('.special-price', true, $html);
        self::assertSelectCount('.old-price', true, $html);
    }

    /**
     * @return array
     */
    public function isProductListDataProvider()
    {
        return [
            'is_not_product_list' => [false],
            'is_product_list' => [true],
        ];
    }
}
