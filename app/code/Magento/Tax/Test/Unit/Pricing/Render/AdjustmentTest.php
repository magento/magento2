<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Pricing\Render;

use Magento\Catalog\Model\Product;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Amount\Base;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Pricing\Render\Amount;
use Magento\Framework\Pricing\Render\AmountRenderInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\View\Element\Template\Context;
use Magento\Tax\Helper\Data;
use Magento\Tax\Pricing\Render\Adjustment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdjustmentTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $contextMock;

    /**
     * Price currency model mock
     *
     * @var PriceCurrency|MockObject
     */
    protected $priceCurrencyMock;

    /**
     * Price helper mock
     *
     * @var \Magento\Tax\Helper\Data|MockObject
     */
    protected $taxHelperMock;

    /**
     * @var Adjustment
     */
    protected $model;

    /**
     * @var AmountRenderInterface
     */
    protected $amountRender;

    /**
     * Init mocks and model
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createPartialMockWithReflection(
            Context::class,
            ['getStoreConfig', 'getEventManager', 'getScopeConfig']
        );
        $this->priceCurrencyMock = $this->createMock(PriceCurrency::class);
        $this->taxHelperMock = $this->createMock(Data::class);

        $eventManagerMock = $this->createMock(ManagerInterface::class);

        $scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $this->contextMock->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($scopeConfigMock);

        $this->model = new Adjustment(
            $this->contextMock,
            $this->priceCurrencyMock,
            $this->taxHelperMock
        );
    }

    /**
     * Test for method getAdjustmentCode
     */
    public function testGetAdjustmentCode(): void
    {
        $this->assertEquals(\Magento\Tax\Pricing\Adjustment::ADJUSTMENT_CODE, $this->model->getAdjustmentCode());
    }

    /**
     * Test for method getDefaultExclusions
     */
    public function testGetDefaultExclusions(): void
    {
        $defaultExclusions = $this->model->getDefaultExclusions();
        $this->assertNotEmpty($defaultExclusions, 'Expected to have at least one default exclusion');
        $this->assertContains($this->model->getAdjustmentCode(), $defaultExclusions);
    }

    /**
     * Test for method displayBothPrices
     */
    public function testDisplayBothPrices(): void
    {
        $shouldDisplayBothPrices = true;
        $this->taxHelperMock->expects($this->once())
            ->method('displayBothPrices')
            ->willReturn($shouldDisplayBothPrices);
        $this->assertEquals($shouldDisplayBothPrices, $this->model->displayBothPrices());
    }

    /**
     * Test for method getDisplayAmountExclTax
     */
    public function testGetDisplayAmountExclTax(): void
    {
        $expectedPriceValue = 1.23;
        $expectedPrice = '$4.56';

        /** @var Amount $amountRender */
        $amountRender = $this->getMockBuilder(Amount::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAmount'])
            ->getMock();

        /** @var Base $baseAmount */
        $baseAmount = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
            ->getMock();

        $baseAmount->expects($this->any())
            ->method('getValue')
            ->willReturn($expectedPriceValue);

        $amountRender->expects($this->any())
            ->method('getAmount')
            ->willReturn($baseAmount);

        $this->priceCurrencyMock->expects($this->any())
            ->method('format')
            ->willReturn($expectedPrice);

        $this->model->render($amountRender);
        $result = $this->model->getDisplayAmountExclTax();

        $this->assertEquals($expectedPrice, $result);
    }

    /**
     * Test for method getDisplayAmount
     */
    #[DataProvider('getDisplayAmountDataProvider')]
    public function testGetDisplayAmount(bool $includeContainer): void
    {
        $expectedPriceValue = 1.23;
        $expectedPrice = '$4.56';

        /** @var Amount $amountRender */
        $amountRender = $this->getMockBuilder(Amount::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAmount'])
            ->getMock();
        /** @var Base $baseAmount */
        $baseAmount = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
            ->getMock();

        $baseAmount->expects($this->any())
            ->method('getValue')
            ->willReturn($expectedPriceValue);

        $amountRender->expects($this->any())
            ->method('getAmount')
            ->willReturn($baseAmount);

        $this->priceCurrencyMock->expects($this->any())
            ->method('format')
            ->with($this->anything(), $includeContainer)
            ->willReturn($expectedPrice);

        $this->model->render($amountRender);
        $result = $this->model->getDisplayAmount($includeContainer);

        $this->assertEquals($expectedPrice, $result);
    }

    /**
     * Data provider for testGetDisplayAmount
     *
     * @return array
     */
    public static function getDisplayAmountDataProvider(): array
    {
        return [[true], [false]];
    }

    /**
     * Test for method buildIdWithPrefix
     */
    #[DataProvider('buildIdWithPrefixDataProvider')]
    public function testBuildIdWithPrefix(string $prefix, $saleableId, $suffix, string $expectedResult): void
    {
        /** @var Amount $amountRender */
        $amountRender = $this->getMockBuilder(Amount::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSaleableItem'])
            ->getMock();

        /** @var Product $saleable */
        $saleable = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', '__wakeup'])
            ->getMock();

        $amountRender->expects($this->any())
            ->method('getSaleableItem')
            ->willReturn($saleable);
        $saleable->expects($this->any())
            ->method('getId')
            ->willReturn($saleableId);

        $this->model->setIdSuffix($suffix);
        $this->model->render($amountRender);
        $result = $this->model->buildIdWithPrefix($prefix);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * data provider for testBuildIdWithPrefix
     *
     * @return array
     */
    public static function buildIdWithPrefixDataProvider(): array
    {
        return [
            ['some_prefix_', null, '_suffix', 'some_prefix__suffix'],
            ['some_prefix_', false, '_suffix', 'some_prefix__suffix'],
            ['some_prefix_', 123, '_suffix', 'some_prefix_123_suffix'],
            ['some_prefix_', 123, null, 'some_prefix_123'],
            ['some_prefix_', 123, false, 'some_prefix_123'],
        ];
    }

    /**
     * test for method displayPriceIncludingTax
     */
    public function testDisplayPriceIncludingTax(): void
    {
        $expectedResult = true;

        $this->taxHelperMock->expects($this->once())
            ->method('displayPriceIncludingTax')
            ->willReturn($expectedResult);

        $result = $this->model->displayPriceIncludingTax();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * test for method displayPriceExcludingTax
     */
    public function testDisplayPriceExcludingTax(): void
    {
        $expectedResult = true;

        $this->taxHelperMock->expects($this->once())
            ->method('displayPriceExcludingTax')
            ->willReturn($expectedResult);

        $result = $this->model->displayPriceExcludingTax();

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetHtmlExcluding(): void
    {
        $arguments = [];
        $displayValue = 8.0;

        $amountRender = $this->createMock(AmountRenderInterface::class);
        $amountMock = $this->createMock(AmountInterface::class);
        $amountMock->expects($this->once())
            ->method('getValue')
            ->with(\Magento\Tax\Pricing\Adjustment::ADJUSTMENT_CODE)
            ->willReturn($displayValue);

        $this->taxHelperMock->expects($this->once())
            ->method('displayBothPrices')
            ->willReturn(false);
        $this->taxHelperMock->expects($this->once())
            ->method('displayPriceExcludingTax')
            ->willReturn(true);

        $amountRender->expects($this->once())
            ->method('setDisplayValue')
            ->with($displayValue);
        $amountRender->expects($this->once())
            ->method('getAmount')
            ->willReturn($amountMock);

        $this->model->render($amountRender, $arguments);
    }

    public function testGetHtmlBoth(): void
    {
        $arguments = [];
        $this->model->setZone(Render::ZONE_ITEM_VIEW);

        $amountRender = $this->createPartialMockWithReflection(
            Amount::class,
            ['setPriceDisplayLabel', 'setPriceWrapperCss', 'setPriceId', 'getSaleableItem']
        );
        $product = $this->createMock(SaleableInterface::class);
        $product->expects($this->once())
            ->method('getId');

        $this->taxHelperMock->expects($this->once())
            ->method('displayBothPrices')
            ->willReturn(true);

        $amountRender->expects($this->once())
            ->method('setPriceDisplayLabel');
        $amountRender->expects($this->once())
            ->method('getSaleableItem')
            ->willReturn($product);
        $amountRender->expects($this->once())
            ->method('setPriceId');
        $amountRender->expects($this->once())
            ->method('setPriceWrapperCss');

        $this->model->render($amountRender, $arguments);
    }

    /**
     * test for method getDataPriceType
     */
    #[DataProvider('dataPriceTypeDataProvider')]
    public function testGetDataPriceType(?string $priceType, string $priceTypeValue): void
    {
        $amountRender = $this->createPartialMockWithReflection(
            Amount::class,
            ['getPriceType']
        );
        $amountRender->expects($this->atLeastOnce())
            ->method('getPriceType')
            ->willReturn($priceType);
        $this->model->render($amountRender, []);
        //no exception is thrown
        $this->assertEquals($priceTypeValue, $this->model->getDataPriceType());
        $this->assertIsString($this->model->getDataPriceType());
    }

    /**
     * data provider for testGetDataPriceType
     *
     * @return array
     */
    public static function dataPriceTypeDataProvider(): array
    {
        return [['finalPrice', 'basePrice'], [null, '']];
    }
}
