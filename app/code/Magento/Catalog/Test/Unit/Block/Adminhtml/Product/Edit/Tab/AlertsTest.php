<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Tab;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Backend\Block\Widget\Accordion;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts\Price;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts\Stock;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AlertsTest extends TestCase
{
    /**
     * @var Alerts
     */
    protected Alerts $alerts;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected ScopeConfigInterface|MockObject $scopeConfigMock;

    /**
     * @var LayoutInterface|MockObject
     */
    protected LayoutInterface|MockObject $layoutMock;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->layoutMock = $this->createMock(LayoutInterface::class);

        $this->alerts = $helper->getObject(
            Alerts::class,
            ['scopeConfig' => $this->scopeConfigMock]
        );
    }

    /**
     * Helper method to set up layout mock with accordion and alert blocks
     *
     * @param bool $includePriceBlock
     * @param bool $includeStockBlock
     * @param Accordion|null $accordionMock
     * @return Accordion&MockObject
     */
    private function setupLayoutMock(
        bool $includePriceBlock = false,
        bool $includeStockBlock = false,
        ?Accordion $accordionMock = null
    ): MockObject {
        if ($accordionMock === null) {
            $accordionMock = $this->getMockBuilder(Accordion::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['addItem'])
                ->addMethods(['setId'])
                ->getMock();

            $accordionMock->method('setId')
                ->with('productAlerts')
                ->willReturnSelf();
        }

        $blockMap = [
            [Accordion::class, '', [], $accordionMock]
        ];

        if ($includePriceBlock) {
            $priceBlockMock = $this->getMockBuilder(Price::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['toHtml'])
                ->getMock();
            $priceBlockMock->method('toHtml')->willReturn('<price-content>');
            $blockMap[] = [Price::class, '', [], $priceBlockMock];
        }

        if ($includeStockBlock) {
            $stockBlockMock = $this->createMock(Stock::class);
            $blockMap[] = [Stock::class, '', [], $stockBlockMock];
        }

        $this->layoutMock->method('createBlock')
            ->willReturnMap($blockMap);
        $this->alerts->setLayout($this->layoutMock);

        return $accordionMock;
    }

    /**
     * Create accordion mock with standard configuration
     *
     * @return Accordion&MockObject
     */
    private function createAccordionMock(): MockObject
    {
        $accordionMock = $this->getMockBuilder(Accordion::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addItem'])
            ->addMethods(['setId'])
            ->getMock();

        $accordionMock->method('setId')->willReturnSelf();

        return $accordionMock;
    }

    /**
     * Create price block mock
     *
     * @return Price&MockObject
     */
    private function createPriceBlockMock(): MockObject
    {
        $priceBlockMock = $this->getMockBuilder(Price::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toHtml'])
            ->getMock();
        $priceBlockMock->method('toHtml')->willReturn('<price-content>');

        return $priceBlockMock;
    }

    /**
     * Create stock block mock
     *
     * @return Stock&MockObject
     */
    private function createStockBlockMock(): MockObject
    {
        return $this->createMock(Stock::class);
    }

    /**
     * Configure scope config mock for alert settings
     *
     * @param bool $priceAllow
     * @param bool $stockAllow
     * @return void
     */
    private function configureScopeConfig(bool $priceAllow, bool $stockAllow): void
    {
        $this->scopeConfigMock->method('getValue')
            ->willReturnMap([
                ['catalog/productalert/allow_price', ScopeInterface::SCOPE_STORE, null, $priceAllow],
                ['catalog/productalert/allow_stock', ScopeInterface::SCOPE_STORE, null, $stockAllow],
            ]);
    }

    /**
     * Setup layout mock with createBlock callback
     *
     * @param Accordion&MockObject $accordionMock
     * @param Price&MockObject|null $priceBlockMock
     * @param Stock&MockObject|null $stockBlockMock
     * @return void
     */
    private function setupLayoutWithCallback(
        MockObject $accordionMock,
        ?MockObject $priceBlockMock = null,
        ?MockObject $stockBlockMock = null
    ): void {
        $this->layoutMock->method('createBlock')
            ->willReturnCallback(function ($class) use ($accordionMock, $priceBlockMock, $stockBlockMock) {
                if ($class === Accordion::class) {
                    return $accordionMock;
                }
                if ($class === Price::class && $priceBlockMock !== null) {
                    return $priceBlockMock;
                }
                if ($class === Stock::class && $stockBlockMock !== null) {
                    return $stockBlockMock;
                }
                return null;
            });

        $this->alerts->setLayout($this->layoutMock);
    }

    /**
     * Invoke protected _prepareLayout method via reflection
     *
     * @return mixed
     * @throws \ReflectionException
     */
    private function invokePrepareLayout()
    {
        $reflection = new \ReflectionClass($this->alerts);
        $method = $reflection->getMethod('_prepareLayout');
        return $method->invoke($this->alerts);
    }

    /**
     * Test canShowTab returns correct value based on config settings
     *
     * @param bool $priceAllow
     * @param bool $stockAllow
     * @param bool $canShowTab
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts::canShowTab
     * @return void
     */
    #[DataProvider('canShowTabDataProvider')]
    public function testCanShowTab($priceAllow, $stockAllow, $canShowTab): void
    {
        $valueMap = [
            [
                'catalog/productalert/allow_price',
                ScopeInterface::SCOPE_STORE,
                null,
                $priceAllow,
            ],
            [
                'catalog/productalert/allow_stock',
                ScopeInterface::SCOPE_STORE,
                null,
                $stockAllow
            ],
        ];
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturnMap($valueMap);
        $this->assertEquals($canShowTab, $this->alerts->canShowTab());
    }

    /**
     * Test that accordion is created with correct ID
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts::_prepareLayout
     * @return void
     * @throws \ReflectionException
     */
    public function testPrepareLayoutCreatesAccordionWithCorrectId(): void
    {
        $this->scopeConfigMock->method('getValue')->willReturn(false);

        $accordionMock = $this->getMockBuilder(Accordion::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addItem'])
            ->addMethods(['setId'])
            ->getMock();

        $accordionMock->expects($this->atLeastOnce())
            ->method('setId')
            ->with('productAlerts')
            ->willReturnSelf();

        $this->layoutMock->method('createBlock')
            ->with(Accordion::class)
            ->willReturn($accordionMock);
        $this->alerts->setLayout($this->layoutMock);

        $this->invokePrepareLayout();
    }

    /**
     * Test that price alert item is added when price alerts are enabled
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts::_prepareLayout
     * @return void
     * @throws \ReflectionException
     */
    public function testPrepareLayoutAddsPriceAlertWhenEnabled(): void
    {
        $this->configureScopeConfig(true, false);

        $accordionMock = $this->createAccordionMock();
        $accordionMock->expects($this->atLeastOnce())
            ->method('addItem')
            ->with(
                'price',
                $this->callback(function ($config) {
                    return isset($config['title'])
                        && $config['title'] instanceof Phrase
                        && (string)$config['title'] === 'Price Alert Subscriptions'
                        && isset($config['content'])
                        && isset($config['open'])
                        && $config['open'] === true;
                })
            );

        $priceBlockMock = $this->createPriceBlockMock();
        $this->setupLayoutWithCallback($accordionMock, $priceBlockMock);
        $this->invokePrepareLayout();
    }

    /**
     * Test that stock alert item is added when stock alerts are enabled
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts::_prepareLayout
     * @return void
     * @throws \ReflectionException
     */
    public function testPrepareLayoutAddsStockAlertWhenEnabled(): void
    {
        $this->configureScopeConfig(false, true);

        $accordionMock = $this->createAccordionMock();
        $accordionMock->expects($this->atLeastOnce())
            ->method('addItem')
            ->with(
                'stock',
                $this->callback(function ($config) {
                    return isset($config['title'])
                        && $config['title'] instanceof Phrase
                        && (string)$config['title'] === 'Stock Alert Subscriptions'
                        && isset($config['content'])
                        && isset($config['open'])
                        && $config['open'] === true;
                })
            );

        $stockBlockMock = $this->createStockBlockMock();
        $this->setupLayoutWithCallback($accordionMock, null, $stockBlockMock);
        $this->invokePrepareLayout();
    }

    /**
     * Test that both price and stock alert items are added when both are enabled
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts::_prepareLayout
     * @return void
     * @throws \ReflectionException
     */
    public function testPrepareLayoutAddsBothAlertsWhenBothEnabled(): void
    {
        $this->configureScopeConfig(true, true);

        $accordionMock = $this->createAccordionMock();

        $priceAdded = false;
        $stockAdded = false;
        $accordionMock->expects($this->atLeast(2))
            ->method('addItem')
            ->willReturnCallback(function ($id, $config) use (&$priceAdded, &$stockAdded) {
                if ($id === 'price') {
                    $this->assertInstanceOf(Phrase::class, $config['title']);
                    $this->assertEquals('Price Alert Subscriptions', (string)$config['title']);
                    $this->assertTrue($config['open']);
                    $priceAdded = true;
                } elseif ($id === 'stock') {
                    $this->assertInstanceOf(Phrase::class, $config['title']);
                    $this->assertEquals('Stock Alert Subscriptions', (string)$config['title']);
                    $this->assertTrue($config['open']);
                    $stockAdded = true;
                }
            });

        $priceBlockMock = $this->createPriceBlockMock();
        $stockBlockMock = $this->createStockBlockMock();

        $this->setupLayoutWithCallback($accordionMock, $priceBlockMock, $stockBlockMock);
        $this->invokePrepareLayout();

        $this->assertTrue($priceAdded, 'Price alert should be added');
        $this->assertTrue($stockAdded, 'Stock alert should be added');
    }

    /**
     * Test that no alert items are added when both alerts are disabled
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts::_prepareLayout
     * @return void
     * @throws \ReflectionException
     */
    public function testPrepareLayoutAddsNoAlertsWhenBothDisabled(): void
    {
        $this->configureScopeConfig(false, false);

        $accordionMock = $this->createAccordionMock();
        $accordionMock->expects($this->never())
            ->method('addItem');

        $this->setupLayoutMock(false, false, $accordionMock);
        $this->invokePrepareLayout();
    }

    /**
     * Test that accordion is set as child block
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts::_prepareLayout
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts::getAccordionHtml
     * @return void
     * @throws \ReflectionException
     */
    public function testPrepareLayoutSetsAccordionAsChild(): void
    {
        $this->scopeConfigMock->method('getValue')->willReturn(false);

        $this->setupLayoutMock(false, false);
        $this->invokePrepareLayout();

        $accordionHtml = $this->alerts->getAccordionHtml();
        $this->assertIsString($accordionHtml);
    }

    /**
     * Test that price alert content includes Price block HTML
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts::_prepareLayout
     * @return void
     * @throws \ReflectionException
     */
    public function testPrepareLayoutPriceAlertContentIncludesPriceBlockHtml(): void
    {
        $this->configureScopeConfig(true, false);

        $accordionMock = $this->createAccordionMock();
        $accordionMock->expects($this->atLeastOnce())
            ->method('addItem')
            ->with(
                'price',
                $this->callback(function ($config) {
                    return isset($config['content'])
                        && str_contains($config['content'], '<price-content>');
                })
            );

        $priceBlockMock = $this->createPriceBlockMock();
        $this->setupLayoutWithCallback($accordionMock, $priceBlockMock);
        $this->invokePrepareLayout();
    }

    /**
     * @return array
     */
    public static function canShowTabDataProvider()
    {
        return [
            'alert_price_and_stock_allow' => [true, true, true],
            'alert_price_is_allowed_and_stock_is_unallowed' => [true, false, true],
            'alert_price_is_unallowed_and_stock_is_allowed' => [false, true, true],
            'alert_price_is_unallowed_and_stock_is_unallowed' => [false, false, false]
        ];
    }
}
