<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Pricing\Render;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Pricing\Render\Amount;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Framework\View\TemplateEngine\Php;
use Magento\Framework\View\TemplateEnginePool;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FinalPriceBoxTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductInterface
     */
    private $saleableItem;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var FinalPriceBox
     */
    private $finalPriceBox;

    /**
     * @var FinalPrice
     */
    private $finalPrice;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var RendererPool
     */
    private $rendererPool;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var Php
     */
    private $phtml;

    /**
     * @var TemplateEnginePool
     */
    private $templateEnginePool;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);

        $this->appState = $this->objectManager->get(State::class);
        $this->appState->setAreaCode(Area::AREA_FRONTEND);

        $this->phtml = $this->objectManager->create(Php::class);

        $this->templateEnginePool = $this->objectManager->get(TemplateEnginePool::class);

        $enginesReflection = new \ReflectionProperty(
            $this->templateEnginePool,
            'engines'
        );
        $enginesReflection->setAccessible(true);
        $enginesReflection->setValue($this->templateEnginePool, ['phtml' => $this->phtml]);

        $this->rendererPool = $this->objectManager->create(
            RendererPool::class
        );

        $this->rendererPool->setData(
            [
                'default' =>
                    [
                        'default_amount_render_class' => Amount::class,
                        'default_amount_render_template' => 'Magento_Catalog::product/price/amount/default.phtml'
                    ]
            ]
        );

        $this->saleableItem = $this->productRepository->get('tier_prices');
        $this->finalPrice = $this->objectManager->create(
            FinalPrice::class,
            [
                'saleableItem' => $this->saleableItem,
                'quantity' => null
            ]
        );

        $this->finalPriceBox = $this->objectManager->create(
            FinalPriceBox::class,
            [
                'saleableItem' => $this->saleableItem,
                'price' => $this->finalPrice,
                'rendererPool' => $this->rendererPool
            ]
        );

        $this->finalPriceBox->setData('price_id', 'test_price_id');
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_has_tier_price_show_as_low_as.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testRenderAmountMinimalProductWithTierPricesShouldShowMinTierPrice()
    {
        $result = $this->finalPriceBox->renderAmountMinimal();
        $this->assertStringContainsString('$5.00', $result);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_different_store_prices.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     */
    public function testProductSetDifferentStorePricesWithoutTierPriceShouldNotShowAsLowAs()
    {
        $this->assertEmpty($this->finalPriceBox->renderAmountMinimal());
    }
}
