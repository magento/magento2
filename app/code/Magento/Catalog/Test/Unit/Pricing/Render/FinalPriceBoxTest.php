<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Pricing\Render;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Catalog\Pricing\Render\FinalPriceBox;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Event\Test\Unit\ManagerStub;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\Render\Amount;
use Magento\Framework\Pricing\Render\PriceBox;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template\File\Resolver;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutInterface;
use Magento\Msrp\Pricing\Price\MsrpPrice;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FinalPriceBoxTest extends TestCase
{
    /**
     * @var FinalPriceBox
     */
    protected $object;

    /**
     * @var MockObject
     */
    protected $priceType;

    /**
     * @var MockObject
     */
    protected $priceInfo;

    /**
     * @var MockObject
     */
    protected $priceBox;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layout;

    /**
     * @var Product|MockObject
     */
    protected $product;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $logger;

    /**
     * @var RendererPool|MockObject
     */
    protected $rendererPool;

    /**
     * @var PriceInterface|MockObject
     */
    protected $price;

    /**
     * @var SalableResolverInterface|MockObject
     */
    private $salableResolverMock;

    /**
     * @var MinimalPriceCalculatorInterface|MockObject
     */
    private $minimalPriceCalculator;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMockForAbstractClass();
        \Magento\Framework\App\ObjectManager::setInstance($this->objectManagerMock);
        $this->product = $this->getMockBuilder(Product::class)
            ->addMethods(['getCanShowPrice'])
            ->onlyMethods(['getPriceInfo', 'isSalable', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceInfo = $this->getMockForAbstractClass(PriceInfoInterface::class);
        $this->product->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfo);

        $eventManager = $this->createMock(ManagerStub::class);
        $this->layout = $this->createMock(Layout::class);
        $this->priceBox = $this->createMock(PriceBox::class);
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->layout->expects($this->any())->method('getBlock')->willReturn($this->priceBox);

        $cacheState = $this->getMockBuilder(StateInterface::class)
            ->getMockForAbstractClass();

        $appState = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resolver = $this->getMockBuilder(Resolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();

        $store = $this->getMockBuilder(StoreInterface::class)
            ->getMockForAbstractClass();
        $storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->onlyMethods(['getStore'])
            ->addMethods(['getCode'])
            ->getMockForAbstractClass();
        $storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $context = $this->createMock(Context::class);
        $context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManager);
        $context->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layout);
        $context->expects($this->any())
            ->method('getLogger')
            ->willReturn($this->logger);
        $context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($scopeConfigMock);
        $context->expects($this->any())
            ->method('getCacheState')
            ->willReturn($cacheState);
        $context->expects($this->any())
            ->method('getStoreManager')
            ->willReturn($storeManager);
        $context->expects($this->any())
            ->method('getAppState')
            ->willReturn($appState);
        $context->expects($this->any())
            ->method('getResolver')
            ->willReturn($resolver);
        $context->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($urlBuilder);

        $this->rendererPool = $this->getMockBuilder(RendererPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->price = $this->getMockForAbstractClass(PriceInterface::class);
        $this->price->expects($this->any())
            ->method('getPriceCode')
            ->willReturn(FinalPrice::PRICE_CODE);

        $objectManager = new ObjectManager($this);
        $this->salableResolverMock = $this->getMockBuilder(SalableResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->deploymentConfig = $this->createPartialMock(
            DeploymentConfig::class,
            ['get']
        );

        $this->minimalPriceCalculator = $this->getMockForAbstractClass(MinimalPriceCalculatorInterface::class);
        $this->object = $objectManager->getObject(
            FinalPriceBox::class,
            [
                'context' => $context,
                'saleableItem' => $this->product,
                'rendererPool' => $this->rendererPool,
                'price' => $this->price,
                'data' => ['zone' => 'test_zone', 'list_category_page' => true],
                'salableResolver' => $this->salableResolverMock,
                'minimalPriceCalculator' => $this->minimalPriceCalculator
            ]
        );
    }

    /**
     * @return void
     */
    public function testRenderMsrpDisabled(): void
    {
        $priceType = $this->createMock(MsrpPrice::class);
        $this->priceInfo->expects($this->once())
            ->method('getPrice')
            ->with('msrp_price')
            ->willReturn($priceType);

        $priceType->expects($this->any())
            ->method('canApplyMsrp')
            ->with($this->product)
            ->willReturn(false);

        $this->salableResolverMock->expects($this->once())->method('isSalable')->with($this->product)->willReturn(true);

        $result = $this->object->toHtml();

        //assert price wrapper
        $this->assertStringStartsWith('<div', $result);
        //assert css_selector
        $this->assertMatchesRegularExpression('/[final_price]/', $result);
    }

    /**
     * @return void
     */
    public function testNotSalableItem(): void
    {
        $this->salableResolverMock
            ->expects($this->once())
            ->method('isSalable')
            ->with($this->product)
            ->willReturn(false);
        $result = $this->object->toHtml();

        $this->assertEmpty($result);
    }

    /**
     * @return void
     */
    public function testRenderMsrpEnabled(): void
    {
        $priceType = $this->createMock(MsrpPrice::class);
        $this->priceInfo->expects($this->once())
            ->method('getPrice')
            ->with('msrp_price')
            ->willReturn($priceType);

        $priceType->expects($this->any())
            ->method('canApplyMsrp')
            ->with($this->product)
            ->willReturn(true);

        $priceType->expects($this->any())
            ->method('isMinimalPriceLessMsrp')
            ->with($this->product)
            ->willReturn(true);

        $priceBoxRender = $this->getMockBuilder(PriceBox::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceBoxRender->expects($this->once())
            ->method('toHtml')
            ->willReturn('test');

        $arguments = [
            'real_price_html' => '',
            'zone' => 'test_zone',
        ];
        $this->rendererPool->expects($this->once())
            ->method('createPriceRender')
            ->with('msrp_price', $this->product, $arguments)
            ->willReturn($priceBoxRender);

        $this->salableResolverMock
            ->expects($this->once())
            ->method('isSalable')
            ->with($this->product)
            ->willReturn(true);

        $result = $this->object->toHtml();

        //assert price wrapper
        $this->assertEquals(
            '<div class="price-box price-final_price" data-role="priceBox" data-product-id="" ' .
            'data-price-box="product-id-">test</div>',
            $result
        );
    }

    /**
     * @return void
     */
    public function testRenderMsrpNotRegisteredException(): void
    {
        $this->logger->expects($this->once())
            ->method('critical');

        $this->priceInfo->expects($this->once())
            ->method('getPrice')
            ->with('msrp_price')
            ->willThrowException(new \InvalidArgumentException());

        $this->salableResolverMock
            ->expects($this->once())
            ->method('isSalable')
            ->with($this->product)
            ->willReturn(true);

        $result = $this->object->toHtml();

        //assert price wrapper
        $this->assertStringStartsWith('<div', $result);
        //assert css_selector
        $this->assertMatchesRegularExpression('/[final_price]/', $result);
    }

    /**
     * @return void
     */
    public function testRenderAmountMinimal(): void
    {
        $priceId = 'price_id';
        $html = 'html';

        $this->object->setData('price_id', $priceId);
        $this->product->expects($this->never())->method('getId');

        $amount = $this->getMockForAbstractClass(AmountInterface::class);

        $this->minimalPriceCalculator->expects($this->once())->method('getAmount')
            ->with($this->product)
            ->willReturn($amount);

        $arguments = [
            'zone' => 'test_zone',
            'list_category_page' => true,
            'display_label' => __('As low as'),
            'price_id' => $priceId,
            'include_container' => false,
            'skip_adjustments' => false
        ];

        $amountRender = $this->createPartialMock(Amount::class, ['toHtml']);
        $amountRender->expects($this->once())
            ->method('toHtml')
            ->willReturn($html);

        $this->rendererPool->expects($this->once())
            ->method('createAmountRender')
            ->with($amount, $this->product, $this->price, $arguments)
            ->willReturn($amountRender);

        $this->assertEquals($html, $this->object->renderAmountMinimal());
    }

    /**
     * @param float $regularPrice
     * @param float $finalPrice
     * @param bool $expectedResult
     *
     * @return void
     * @dataProvider hasSpecialPriceProvider
     */
    public function testHasSpecialPrice(float $regularPrice, float $finalPrice, bool $expectedResult): void
    {
        $regularPriceType = $this->createMock(RegularPrice::class);
        $finalPriceType = $this->createMock(FinalPrice::class);
        $regularPriceAmount = $this->getMockForAbstractClass(AmountInterface::class);
        $finalPriceAmount = $this->getMockForAbstractClass(AmountInterface::class);

        $regularPriceAmount->expects($this->once())
            ->method('getValue')
            ->willReturn($regularPrice);
        $finalPriceAmount->expects($this->once())
            ->method('getValue')
            ->willReturn($finalPrice);

        $regularPriceType->expects($this->once())
            ->method('getAmount')
            ->willReturn($regularPriceAmount);
        $finalPriceType->expects($this->once())
            ->method('getAmount')
            ->willReturn($finalPriceAmount);

        $this->priceInfo
            ->method('getPrice')
            ->withConsecutive([RegularPrice::PRICE_CODE], [FinalPrice::PRICE_CODE])
            ->willReturnOnConsecutiveCalls($regularPriceType, $finalPriceType);

        $this->assertEquals($expectedResult, $this->object->hasSpecialPrice());
    }

    /**
     * @return array
     */
    public function hasSpecialPriceProvider(): array
    {
        return [
            [10.0, 20.0, false],
            [20.0, 10.0, true],
            [10.0, 10.0, false]
        ];
    }

    /**
     * @return void
     */
    public function testShowMinimalPrice(): void
    {
        $minimalPrice = 5.0;
        $finalPrice = 10.0;
        $displayMinimalPrice = true;

        $this->minimalPriceCalculator->expects($this->once())->method('getValue')->with($this->product)
            ->willReturn($minimalPrice);

        $finalPriceAmount = $this->getMockForAbstractClass(AmountInterface::class);
        $finalPriceAmount->expects($this->once())
            ->method('getValue')
            ->willReturn($finalPrice);

        $finalPriceType = $this->createMock(FinalPrice::class);
        $finalPriceType->expects($this->once())
            ->method('getAmount')
            ->willReturn($finalPriceAmount);

        $this->priceInfo->expects($this->once())
            ->method('getPrice')
            ->with(FinalPrice::PRICE_CODE)
            ->willReturn($finalPriceType);

        $this->object->setDisplayMinimalPrice($displayMinimalPrice);
        $this->assertTrue($this->object->showMinimalPrice());
    }

    /**
     * @return void
     */
    public function testHidePrice(): void
    {
        $this->product->expects($this->any())
            ->method('getCanShowPrice')
            ->willReturn(false);

        $this->assertEmpty($this->object->toHtml());
    }

    /**
     * @return void
     */
    public function testGetCacheKey(): void
    {
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->with(DeploymentConfig::class)
            ->willReturn($this->deploymentConfig);

        $this->deploymentConfig->expects($this->any())
            ->method('get')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY)
            ->willReturn('448198e08af35844a42d3c93c1ef4e03');
        $result = $this->object->getCacheKey();
        $this->assertStringEndsWith('list-category-page', $result);
    }

    /**
     * @return void
     */
    public function testGetCacheKeyInfoContainsDisplayMinimalPrice(): void
    {
        $this->assertArrayHasKey('display_minimal_price', $this->object->getCacheKeyInfo());
    }

    /**
     * Test when is_product_list flag is not specified.
     *
     * @return void
     */
    public function testGetCacheKeyInfoContainsIsProductListFlagByDefault(): void
    {
        $cacheInfo = $this->object->getCacheKeyInfo();
        self::assertArrayHasKey('is_product_list', $cacheInfo);
        self::assertFalse($cacheInfo['is_product_list']);
    }

    /**
     * Test when is_product_list flag is specified.
     *
     * @param bool $flag
     *
     * @return void
     * @dataProvider isProductListDataProvider
     */
    public function testGetCacheKeyInfoContainsIsProductListFlag($flag): void
    {
        $this->object->setData('is_product_list', $flag);
        $cacheInfo = $this->object->getCacheKeyInfo();
        self::assertArrayHasKey('is_product_list', $cacheInfo);
        self::assertEquals($flag, $cacheInfo['is_product_list']);
    }

    /**
     * Test when is_product_list flag is not specified.
     *
     * @return void
     */
    public function testIsProductListByDefault(): void
    {
        self::assertFalse($this->object->isProductList());
    }

    /**
     * Test when is_product_list flag is specified.
     *
     * @param bool $flag
     *
     * @return void
     * @dataProvider isProductListDataProvider
     */
    public function testIsProductList($flag): void
    {
        $this->object->setData('is_product_list', $flag);
        self::assertEquals($flag, $this->object->isProductList());
    }

    /**
     * @return array
     */
    public function isProductListDataProvider(): array
    {
        return [
            'is_not_product_list' => [false],
            'is_product_list' => [true]
        ];
    }
}
