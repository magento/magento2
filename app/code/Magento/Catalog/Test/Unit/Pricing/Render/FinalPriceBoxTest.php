<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Pricing\Render;

use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Render\Amount;
use Magento\Catalog\Pricing\Price\FinalPrice;

/**
 * Class FinalPriceBoxTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FinalPriceBoxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Pricing\Render\FinalPriceBox
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceType;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfo;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceBox;

    /**
     * @var \Magento\Framework\View\LayoutInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Pricing\Render\RendererPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rendererPool;

    /**
     * @var \Magento\Framework\Pricing\Price\PriceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $price;

    /**
     * @var SalableResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $salableResolverMock;

    /** @var ObjectManager */
    private $objectManager;

    /** @var  Manager|\PHPUnit_Framework_MockObject_MockObject */
    private $moduleManager;

    /**
     * @var MinimalPriceCalculatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $minimalPriceCalculator;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getPriceInfo', '__wakeup', 'getCanShowPrice'],
            [],
            '',
            false
        );
        $this->priceInfo = $this->getMock(
            \Magento\Framework\Pricing\PriceInfoInterface::class,
            [
                'getPrice',
                'getPrices',
                'getAdjustments',
                'getAdjustment'
            ],
            [],
            '',
            false
        );
        $this->product->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfo));

        $eventManager = $this->getMock(\Magento\Framework\Event\Test\Unit\ManagerStub::class, [], [], '', false);
        $config = $this->getMock(\Magento\Store\Api\Data\StoreConfigInterface::class, [], [], '', false);
        $this->layout = $this->getMock(\Magento\Framework\View\Layout::class, [], [], '', false);

        $this->priceBox = $this->getMock(\Magento\Framework\Pricing\Render\PriceBox::class, [], [], '', false);
        $this->logger = $this->getMock(\Psr\Log\LoggerInterface::class);

        $this->layout->expects($this->any())->method('getBlock')->willReturn($this->priceBox);

        $cacheState = $this->getMockBuilder(\Magento\Framework\App\Cache\StateInterface::class)
            ->getMockForAbstractClass();

        $appState = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resolver = $this->getMockBuilder(\Magento\Framework\View\Element\Template\File\Resolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)->getMockForAbstractClass();

        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)->getMockForAbstractClass();
        $storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->setMethods(['getStore', 'getCode'])
            ->getMockForAbstractClass();
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $scopeConfigMock = $this->getMockForAbstractClass(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $context = $this->getMock(\Magento\Framework\View\Element\Template\Context::class, [], [], '', false);
        $context->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($eventManager));
        $context->expects($this->any())
            ->method('getStoreConfig')
            ->will($this->returnValue($config));
        $context->expects($this->any())
            ->method('getLayout')
            ->will($this->returnValue($this->layout));
        $context->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($this->logger));
        $context->expects($this->any())
            ->method('getScopeConfig')
            ->will($this->returnValue($scopeConfigMock));
        $context->expects($this->any())
            ->method('getCacheState')
            ->will($this->returnValue($cacheState));
        $context->expects($this->any())
            ->method('getStoreManager')
            ->will($this->returnValue($storeManager));
        $context->expects($this->any())
            ->method('getAppState')
            ->will($this->returnValue($appState));
        $context->expects($this->any())
            ->method('getResolver')
            ->will($this->returnValue($resolver));
        $context->expects($this->any())
            ->method('getUrlBuilder')
            ->will($this->returnValue($urlBuilder));

        $this->rendererPool = $this->getMockBuilder(\Magento\Framework\Pricing\Render\RendererPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->price = $this->getMock(\Magento\Framework\Pricing\Price\PriceInterface::class);
        $this->price->expects($this->any())
            ->method('getPriceCode')
            ->will($this->returnValue(FinalPrice::PRICE_CODE));

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->salableResolverMock = $this->getMockBuilder(SalableResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->minimalPriceCalculator =$this->getMockBuilder(MinimalPriceCalculatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->object = $this->objectManager->getObject(
            \Magento\Catalog\Pricing\Render\FinalPriceBox::class,
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

        $this->moduleManager = $this->getMockBuilder(Manager::class)
            ->setMethods(['isEnabled', 'isOutputEnabled'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager->setBackwardCompatibleProperty(
            $this->object,
            'moduleManager',
            $this->moduleManager
        );
    }

    public function testRenderMsrpDisabled()
    {
        $priceType = $this->getMock(\Magento\Msrp\Pricing\Price\MsrpPrice::class, [], [], '', false);

        $this->moduleManager->expects(self::once())
            ->method('isEnabled')
            ->with('Magento_Msrp')
            ->willReturn(true);

        $this->moduleManager->expects(self::once())
            ->method('isOutputEnabled')
            ->with('Magento_Msrp')
            ->willReturn(true);

        $this->priceInfo->expects($this->once())
            ->method('getPrice')
            ->with($this->equalTo('msrp_price'))
            ->will($this->returnValue($priceType));

        $priceType->expects($this->any())
            ->method('canApplyMsrp')
            ->with($this->equalTo($this->product))
            ->will($this->returnValue(false));

        $result = $this->object->toHtml();

        //assert price wrapper
        $this->assertStringStartsWith('<div', $result);
        //assert css_selector
        $this->assertRegExp('/[final_price]/', $result);
    }

    public function testRenderMsrpEnabled()
    {
        $priceType = $this->getMock(\Magento\Msrp\Pricing\Price\MsrpPrice::class, [], [], '', false);

        $this->moduleManager->expects(self::once())
            ->method('isEnabled')
            ->with('Magento_Msrp')
            ->willReturn(true);

        $this->moduleManager->expects(self::once())
            ->method('isOutputEnabled')
            ->with('Magento_Msrp')
            ->willReturn(true);

        $this->priceInfo->expects($this->once())
            ->method('getPrice')
            ->with($this->equalTo('msrp_price'))
            ->will($this->returnValue($priceType));

        $priceType->expects($this->any())
            ->method('canApplyMsrp')
            ->with($this->equalTo($this->product))
            ->will($this->returnValue(true));

        $priceType->expects($this->any())
            ->method('isMinimalPriceLessMsrp')
            ->with($this->equalTo($this->product))
            ->will($this->returnValue(true));

        $priceBoxRender = $this->getMockBuilder(\Magento\Framework\Pricing\Render\PriceBox::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceBoxRender->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue('test'));

        $arguments = [
            'real_price_html' => '',
            'zone' => 'test_zone',
        ];
        $this->rendererPool->expects($this->once())
            ->method('createPriceRender')
            ->with('msrp_price', $this->product, $arguments)
            ->will($this->returnValue($priceBoxRender));

        $result = $this->object->toHtml();

        //assert price wrapper
        $this->assertEquals(
            '<div class="price-box price-final_price" data-role="priceBox" data-product-id="">test</div>',
            $result
        );
    }

    public function testRenderMsrpNotRegisteredException()
    {
        $this->moduleManager->expects(self::once())
            ->method('isEnabled')
            ->with('Magento_Msrp')
            ->willReturn(true);

        $this->moduleManager->expects(self::once())
            ->method('isOutputEnabled')
            ->with('Magento_Msrp')
            ->willReturn(true);

        $this->logger->expects($this->once())
            ->method('critical');

        $this->priceInfo->expects($this->once())
            ->method('getPrice')
            ->with($this->equalTo('msrp_price'))
            ->will($this->throwException(new \InvalidArgumentException()));

        $result = $this->object->toHtml();

        //assert price wrapper
        $this->assertStringStartsWith('<div', $result);
        //assert css_selector
        $this->assertRegExp('/[final_price]/', $result);
    }

    public function testRenderAmountMinimal()
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
            'display_label' => 'As low as',
            'price_id' => $priceId,
            'include_container' => false,
            'skip_adjustments' => true,
        ];

        $amountRender = $this->getMock(Amount::class, ['toHtml'], [], '', false);
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
     * @dataProvider hasSpecialPriceProvider
     * @param float $regularPrice
     * @param float $finalPrice
     * @param bool $expectedResult
     */
    public function testHasSpecialPrice($regularPrice, $finalPrice, $expectedResult)
    {
        $regularPriceType = $this->getMock(\Magento\Catalog\Pricing\Price\RegularPrice::class, [], [], '', false);
        $finalPriceType = $this->getMock(FinalPrice::class, [], [], '', false);
        $regularPriceAmount = $this->getMockForAbstractClass(AmountInterface::class);
        $finalPriceAmount = $this->getMockForAbstractClass(AmountInterface::class);

        $regularPriceAmount->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($regularPrice));
        $finalPriceAmount->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($finalPrice));

        $regularPriceType->expects($this->once())
            ->method('getAmount')
            ->will($this->returnValue($regularPriceAmount));
        $finalPriceType->expects($this->once())
            ->method('getAmount')
            ->will($this->returnValue($finalPriceAmount));

        $this->priceInfo->expects($this->at(0))
            ->method('getPrice')
            ->with(\Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE)
            ->will($this->returnValue($regularPriceType));
        $this->priceInfo->expects($this->at(1))
            ->method('getPrice')
            ->with(FinalPrice::PRICE_CODE)
            ->will($this->returnValue($finalPriceType));

        $this->assertEquals($expectedResult, $this->object->hasSpecialPrice());
    }

    /**
     * @return array
     */
    public function hasSpecialPriceProvider()
    {
        return [
            [10.0, 20.0, false],
            [20.0, 10.0, true],
            [10.0, 10.0, false]
        ];
    }

    public function testShowMinimalPrice()
    {
        $minimalPrice = 5.0;
        $finalPrice = 10.0;
        $displayMininmalPrice = true;

        $this->minimalPriceCalculator->expects($this->once())->method('getValue')->with($this->product)
            ->willReturn($minimalPrice);

        $finalPriceAmount = $this->getMockForAbstractClass(AmountInterface::class);

        $finalPriceAmount->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($finalPrice));

        $finalPriceType = $this->getMock(FinalPrice::class, [], [], '', false);
        $finalPriceType->expects($this->once())
            ->method('getAmount')
            ->will($this->returnValue($finalPriceAmount));

        $this->priceInfo->expects($this->once())
            ->method('getPrice')
            ->with(FinalPrice::PRICE_CODE)
            ->willReturn($finalPriceType);

        $this->object->setDisplayMinimalPrice($displayMininmalPrice);

        $this->assertTrue($this->object->showMinimalPrice());
    }

    public function testHidePrice()
    {
        $this->product->expects($this->any())
            ->method('getCanShowPrice')
            ->will($this->returnValue(false));

        $this->assertEmpty($this->object->toHtml());
    }

    public function testGetCacheKey()
    {
        $result = $this->object->getCacheKey();
        $this->assertStringEndsWith('list-category-page', $result);
    }

    public function testGetCacheKeyInfoContainsDisplayMinimalPrice()
    {
        $this->assertArrayHasKey('display_minimal_price', $this->object->getCacheKeyInfo());
    }

    public function testRenderMsrpModuleDisabled()
    {
        $this->moduleManager->expects(self::exactly(2))
            ->method('isEnabled')
            ->with('Magento_Msrp')
            ->will($this->onConsecutiveCalls(false, true));

        $this->priceInfo->expects($this->never())
            ->method('getPrice');

        $result = $this->object->toHtml();

        //assert price wrapper
        $this->assertStringStartsWith('<div', $result);
        //assert css_selector
        $this->assertRegExp('/[final_price]/', $result);

        $this->moduleManager->expects(self::once())
            ->method('isOutputEnabled')
            ->with('Magento_Msrp')
            ->willReturn(false);

        $result = $this->object->toHtml();

        //assert price wrapper
        $this->assertStringStartsWith('<div', $result);
        //assert css_selector
        $this->assertRegExp('/[final_price]/', $result);
    }

    /**
     * Test when is_product_list flag is not specified
     */
    public function testGetCacheKeyInfoContainsIsProductListFlagByDefault()
    {
        $cacheInfo = $this->object->getCacheKeyInfo();
        self::assertArrayHasKey('is_product_list', $cacheInfo);
        self::assertFalse($cacheInfo['is_product_list']);
    }

    /**
     * Test when is_product_list flag is specified
     *
     * @param bool $flag
     * @dataProvider isProductListDataProvider
     */
    public function testGetCacheKeyInfoContainsIsProductListFlag($flag)
    {
        $this->object->setData('is_product_list', $flag);
        $cacheInfo = $this->object->getCacheKeyInfo();
        self::assertArrayHasKey('is_product_list', $cacheInfo);
        self::assertEquals($flag, $cacheInfo['is_product_list']);
    }

    /**
     * Test when is_product_list flag is not specified
     */
    public function testIsProductListByDefault()
    {
        self::assertFalse($this->object->isProductList());
    }

    /**
     * Test when is_product_list flag is specified
     *
     * @param bool $flag
     * @dataProvider isProductListDataProvider
     */
    public function testIsProductList($flag)
    {
        $this->object->setData('is_product_list', $flag);
        self::assertEquals($flag, $this->object->isProductList());
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
