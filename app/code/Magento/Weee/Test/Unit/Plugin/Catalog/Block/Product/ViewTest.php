<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Plugin\Catalog\Block\Product;

use Magento\Catalog\Block\Product\View as ProductView;
use Magento\Catalog\Model\Product;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceInfo\Base as PriceInfo;
use Magento\Framework\Pricing\Render;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Pricing\Adjustment as TaxAdjustment;
use Magento\Weee\Helper\Data as WeeeHelper;
use Magento\Weee\Model\Tax;
use Magento\Weee\Plugin\Catalog\Block\Product\View;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The price box keeps Fixed Product Tax when it redraws an excluding-tax price.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewTest extends TestCase
{
    /**
     * @var WeeeHelper|MockObject
     */
    private $weeeHelper;

    /**
     * @var TaxHelper|MockObject
     */
    private $taxHelper;

    /**
     * @var EncoderInterface|MockObject
     */
    private $jsonEncoder;

    /**
     * @var DecoderInterface|MockObject
     */
    private $jsonDecoder;

    /**
     * @var View
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->weeeHelper = $this->createMock(WeeeHelper::class);
        $this->taxHelper = $this->createMock(TaxHelper::class);
        $this->jsonEncoder = $this->createMock(EncoderInterface::class);
        $this->jsonDecoder = $this->createMock(DecoderInterface::class);
        $this->plugin = new View(
            $this->weeeHelper,
            $this->taxHelper,
            $this->jsonEncoder,
            $this->jsonDecoder
        );
    }

    /**
     * Excluding-tax amounts in the price config gain the Fixed Product Tax the HTML already shows.
     */
    public function testExcludingTaxPriceKeepsFixedProductTax(): void
    {
        $jsonConfig = '{"prices":{"basePrice":{"amount":60},"baseOldPrice":{"amount":100},'
            . '"finalPrice":{"amount":74.95},"oldPrice":{"amount":118.25}}}';
        $product = $this->createProduct(
            [
                'final_price' => 70.0,
                'regular_price' => 110.0,
            ]
        );
        $subject = $this->createStub(ProductView::class);
        $subject->method('getProduct')->willReturn($product);
        $this->expectIncludedFixedProductTax();

        $this->jsonDecoder->expects($this->once())
            ->method('decode')
            ->with($jsonConfig)
            ->willReturn([
                'prices' => [
                    'basePrice' => ['amount' => 60],
                    'baseOldPrice' => ['amount' => 100],
                    'finalPrice' => ['amount' => 74.95],
                    'oldPrice' => ['amount' => 118.25],
                ],
            ]);
        $this->jsonEncoder->expects($this->once())
            ->method('encode')
            ->with([
                'prices' => [
                    'basePrice' => ['amount' => 70.0],
                    'baseOldPrice' => ['amount' => 110.0],
                    'finalPrice' => ['amount' => 74.95],
                    'oldPrice' => ['amount' => 118.25],
                ],
            ])
            ->willReturn('{"prices":{"basePrice":{"amount":70}}}');

        $this->assertSame(
            '{"prices":{"basePrice":{"amount":70}}}',
            $this->plugin->afterGetJsonConfig($subject, $jsonConfig)
        );
    }

    /**
     * Other tax and Fixed Product Tax settings leave the price config alone.
     *
     * @param bool $fptEnabled
     * @param bool $priceIncludesTax
     * @param bool $displayExcludingTax
     * @param bool $fptIncludedInPrice
     */
    #[DataProvider('unchangedConfigDataProvider')]
    public function testOtherDisplaySettingsAreUnchanged(
        bool $fptEnabled,
        bool $priceIncludesTax,
        bool $displayExcludingTax,
        bool $fptIncludedInPrice
    ): void {
        $jsonConfig = '{"prices":{"basePrice":{"amount":60}}}';
        $product = $this->createMock(Product::class);
        $product->method('getStoreId')->willReturn(1);
        $product->expects($this->never())->method('getPriceInfo');
        $subject = $this->createStub(ProductView::class);
        $subject->method('getProduct')->willReturn($product);

        $this->weeeHelper->expects($this->atMost(1))->method('isEnabled')->with(1)->willReturn($fptEnabled);
        $this->taxHelper->expects($this->atMost(1))->method('priceIncludesTax')->with(1)->willReturn($priceIncludesTax);
        $this->taxHelper->expects($this->atMost(1))
            ->method('displayPriceExcludingTax')
            ->willReturn($displayExcludingTax);
        $this->weeeHelper->expects($this->atMost(1))->method('typeOfDisplay')->willReturn($fptIncludedInPrice);
        $this->jsonDecoder->expects($this->never())->method('decode');
        $this->jsonEncoder->expects($this->never())->method('encode');

        $this->assertSame($jsonConfig, $this->plugin->afterGetJsonConfig($subject, $jsonConfig));
    }

    /**
     * A product without the option price config has nothing for the price box to redraw.
     */
    public function testConfigWithoutPricesIsUnchanged(): void
    {
        $jsonConfig = '{"productId":5}';
        $product = $this->createMock(Product::class);
        $product->method('getStoreId')->willReturn(1);
        $product->expects($this->never())->method('getPriceInfo');
        $subject = $this->createStub(ProductView::class);
        $subject->method('getProduct')->willReturn($product);
        $this->expectIncludedFixedProductTax();

        $this->jsonDecoder->expects($this->once())
            ->method('decode')
            ->with($jsonConfig)
            ->willReturn(['productId' => 5]);
        $this->jsonEncoder->expects($this->never())->method('encode');

        $this->assertSame($jsonConfig, $this->plugin->afterGetJsonConfig($subject, $jsonConfig));
    }

    /**
     * @return array<string, array<int, bool>>
     */
    public static function unchangedConfigDataProvider(): array
    {
        return [
            'fpt disabled' => [false, true, true, true],
            'catalog prices exclude tax' => [true, false, true, true],
            'catalog displays prices including tax' => [true, true, false, true],
            'fpt is shown separately from the price' => [true, true, true, false],
        ];
    }

    /**
     * @param array<string, float> $amounts
     * @return Product|MockObject
     */
    private function createProduct(array $amounts)
    {
        $prices = [];
        foreach ($amounts as $priceCode => $amount) {
            $amountModel = $this->createMock(AmountInterface::class);
            $amountModel->expects($this->once())
                ->method('getValue')
                ->with(TaxAdjustment::ADJUSTMENT_CODE)
                ->willReturn($amount);
            $price = $this->createStub(PriceInterface::class);
            $price->method('getAmount')->willReturn($amountModel);
            $prices[$priceCode] = $price;
        }

        $priceInfo = $this->createStub(PriceInfo::class);
        $priceInfo->method('getPrice')->willReturnCallback(
            static function (string $priceCode) use ($prices) {
                return $prices[$priceCode];
            }
        );

        $product = $this->createStub(Product::class);
        $product->method('getStoreId')->willReturn(1);
        $product->method('getPriceInfo')->willReturn($priceInfo);

        return $product;
    }

    /**
     * Catalog prices include tax, the catalog shows them excluding tax, and the price includes FPT.
     */
    private function expectIncludedFixedProductTax(): void
    {
        $this->weeeHelper->expects($this->once())->method('isEnabled')->with(1)->willReturn(true);
        $this->taxHelper->expects($this->once())->method('priceIncludesTax')->with(1)->willReturn(true);
        $this->taxHelper->expects($this->once())->method('displayPriceExcludingTax')->willReturn(true);
        $this->weeeHelper->expects($this->once())->method('typeOfDisplay')
            ->with(
                [Tax::DISPLAY_INCL, Tax::DISPLAY_INCL_DESCR],
                Render::ZONE_ITEM_VIEW,
                1
            )
            ->willReturn(true);
    }
}
