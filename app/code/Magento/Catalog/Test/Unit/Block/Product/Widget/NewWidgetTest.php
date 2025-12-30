<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product\Widget;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Block\Product\Context as ProductBlockContext;
use Magento\Catalog\Block\Product\Widget\Html\Pager;
use Magento\Catalog\Block\Product\Widget\NewWidget;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Cache\State;
use Magento\Framework\App\Config;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Layout;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit test for NewWidget block
 *
 * @covers \Magento\Catalog\Block\Product\Widget\NewWidget
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewWidgetTest extends TestCase
{
    /**
     * @var NewWidget
     */
    private NewWidget $block;

    /**
     * @var Layout|MockObject
     */
    private $layout;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ProductBlockContext|MockObject
     */
    private $context;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManager;

    /**
     * @var Manager|MockObject
     */
    private $eventManager;

    /**
     * @var Config|MockObject
     */
    private $scopeConfig;

    /**
     * @var State|MockObject
     */
    private $cacheState;

    /** @var CatalogConfig|MockObject */
    protected $catalogConfig;

    /**
     * @var Timezone|MockObject
     */
    private $localDate;

    /**
     * @var Collection|MockObject
     */
    private $productCollection;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManagerHelper($this);
        $this->eventManager = $this->createPartialMock(Manager::class, ['dispatch']);
        $this->scopeConfig = $this->createMock(Config::class);
        $this->cacheState = $this->createPartialMock(State::class, ['isEnabled']);
        $this->localDate = $this->createMock(Timezone::class);
        $this->catalogConfig = $this->createPartialMock(CatalogConfig::class, ['getProductAttributes']);
        $this->layout = $this->createMock(Layout::class);
        $this->requestMock = $this->createMock(RequestInterface::class);

        $this->context = $this->createPartialMock(ProductBlockContext::class, [
            'getEventManager', 'getScopeConfig', 'getLayout',
            'getRequest', 'getCacheState', 'getCatalogConfig',
            'getLocaleDate'
        ]);

        $this->context->method('getLayout')->willReturn($this->layout);
        $this->context->method('getRequest')->willReturn($this->requestMock);

        $this->block = $this->objectManager->getObject(
            NewWidget::class,
            [
                'context' => $this->context
            ]
        );
    }

    /**
     * Unit test for getProductPriceHtml()
     *
     * @covers \Magento\Catalog\Block\Product\Widget\NewWidget::getProductPriceHtml()
     * @dataProvider getProductPriceHtmlDataProvider
     * @param array $args
     * @return void
     */
    public function testGetProductPriceHtml(array $args): void
    {
        $id = 6;
        $expectedHtml = '
        <div class="price-box price-final_price">
            <span class="regular-price" id="product-price-' . $id . '">
                <span class="price">$0.00</span>
            </span>
        </div>';
        $type = 'widget-new-list';
        $productMock = $this->createPartialMock(Product::class, ['getId']);
        $priceBoxMock = $this->createPartialMock(Render::class, ['render']);

        $productMock->expects($this->any())
            ->method('getId')
            ->willReturn($id);
        $arguments = [
            'price_id' => 'old-price-' . $id . '-' . $type,
            'display_minimal_price' => true,
            'include_container' => true,
            'zone' => Render::ZONE_ITEM_LIST,
        ];

        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->willReturn($priceBoxMock);
        $priceBoxMock->expects($this->once())
            ->method('render')
            ->with('final_price', $productMock, $arguments)
            ->willReturn($expectedHtml);

        $result = $this->block->getProductPriceHtml($productMock, $type, Render::ZONE_ITEM_LIST, $args);
        $this->assertSame($expectedHtml, $result);
    }

    /**
     * Data provider for testGetProductPriceHtml()
     *
     * @return array
     */
    public static function getProductPriceHtmlDataProvider(): array
    {
        return [
            'without-arguments' => [
                []
            ],
            'with-arguments' => [
                [
                    'zone' => Render::ZONE_ITEM_LIST,
                    'price_id' => 'old-price-6-widget-new-list',
                    'include_container' => true,
                    'display_minimal_price' => true
                ]
            ]
        ];
    }

    /**
     * Unit test for getCurrentPage() using data provider
     *
     * @covers \Magento\Catalog\Block\Product\Widget\NewWidget::getCurrentPage()
     * @dataProvider getCurrentPageDataProvider
     * @param int $pageNumber
     * @param int $expectedResult
     */
    #[DataProvider('getCurrentPageDataProvider')]
    public function testGetCurrentPage($pageNumber, $expectedResult)
    {
        $this->block->setData('page_var_name', 'page_number');

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with('page_number')
            ->willReturn($pageNumber);

        $this->assertSame($expectedResult, $this->block->getCurrentPage());
    }

    /**
     * Data provider for testGetCurrentPage()
     *
     * @return array
     */
    public static function getCurrentPageDataProvider(): array
    {
        return [
            'page_one' => [
                'pageNumber' => 1,
                'expectedResult' => 1,
            ],
            'page_five' => [
                'pageNumber' => 5,
                'expectedResult' => 5,
            ],
            'page_ten' => [
                'pageNumber' => 10,
                'expectedResult' => 10,
            ],
            'page_zero' => [
                'pageNumber' => 0,
                'expectedResult' => 0,
            ],
            'negative_page' => [
                'pageNumber' => -5,
                'expectedResult' => 5
            ],
        ];
    }

    /**
     * Unit test for getDisplayType()
     *
     * @covers \Magento\Catalog\Block\Product\Widget\NewWidget::getDisplayType()
     * @return void
     */
    public function testGetDisplayType(): void
    {
        $this->assertSame(NewWidget::DISPLAY_TYPE_ALL_PRODUCTS, $this->block->getDisplayType());
        $this->block->setData('display_type', NewWidget::DISPLAY_TYPE_NEW_PRODUCTS);
        $this->assertSame(NewWidget::DISPLAY_TYPE_NEW_PRODUCTS, $this->block->getDisplayType());
    }

    /**
     * Unit test for showPager()
     *
     * @covers \Magento\Catalog\Block\Product\Widget\NewWidget::showPager()
     * @return void
     */
    public function testShowPager(): void
    {
        $this->assertFalse($this->block->showPager());
        $this->block->setData('show_pager', 10);
        $this->assertTrue($this->block->showPager());
    }

    /**
     * Unit test for getProductsCount()
     *
     * @covers \Magento\Catalog\Block\Product\Widget\NewWidget::getProductsCount()
     * @return void
     */
    public function testGetProductsCount(): void
    {
        $this->assertSame(10, $this->block->getProductsCount());
        $this->block->setProductsCount(2);
        $this->assertSame(2, $this->block->getProductsCount());
    }

    /**
     * Helper function for product collection tests
     *
     * @return void
     */
    protected function generalGetProductCollection(): void
    {
        $this->eventManager->expects($this->exactly(2))->method('dispatch')
            ->willReturn(true);
        $this->scopeConfig->expects($this->once())->method('getValue')->withAnyParameters()
            ->willReturn(false);
        $this->cacheState->expects($this->atLeastOnce())->method('isEnabled')->withAnyParameters()
            ->willReturn(false);
        $this->catalogConfig->expects($this->once())->method('getProductAttributes')
            ->willReturn([]);
        $this->localDate->method('date')->willReturn(new \DateTime('now', new \DateTimeZone('UTC')));

        $this->context->expects($this->once())->method('getEventManager')->willReturn($this->eventManager);
        $this->context->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfig);
        $this->context->expects($this->once())->method('getCacheState')->willReturn($this->cacheState);
        $this->context->expects($this->once())->method('getCatalogConfig')->willReturn($this->catalogConfig);
        $this->context->expects($this->once())->method('getLocaleDate')->willReturn($this->localDate);

        $this->productCollection = $this->createPartialMock(Collection::class, [
            'setVisibility', 'addMinimalPrice', 'addFinalPrice',
            'addTaxPercents', 'addAttributeToSelect', 'addUrlRewrite',
            'addStoreFilter', 'addAttributeToSort', 'setPageSize',
            'setCurPage', 'addAttributeToFilter'
        ]);
        $this->productCollection->expects($this->once())->method('setVisibility')
            ->willReturnSelf();
        $this->productCollection->expects($this->once())->method('addMinimalPrice')
            ->willReturnSelf();
        $this->productCollection->expects($this->once())->method('addFinalPrice')
            ->willReturnSelf();
        $this->productCollection->expects($this->once())->method('addTaxPercents')
            ->willReturnSelf();
        $this->productCollection->expects($this->once())->method('addAttributeToSelect')
            ->willReturnSelf();
        $this->productCollection->expects($this->once())->method('addUrlRewrite')
            ->willReturnSelf();
        $this->productCollection->expects($this->once())->method('addStoreFilter')
            ->willReturnSelf();
        $this->productCollection->expects($this->once())->method('addAttributeToSort')
            ->willReturnSelf();
        $this->productCollection->expects($this->atLeastOnce())->method('setCurPage')
            ->willReturnSelf();
        $this->productCollection->expects($this->any())->method('addAttributeToFilter')
            ->willReturnSelf();
    }

    /**
     * Helper function to start test for getProductCollection
     *
     * @param string $displayType
     * @param bool $pagerEnable
     * @param int $productsCount
     * @param int|null $productsPerPage
     * @return void
     */
    protected function startTestGetProductCollection(
        string $displayType,
        bool   $pagerEnable,
        int    $productsCount,
        ?int   $productsPerPage
    ): void {
        $productCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $productCollectionFactory->expects($this->atLeastOnce())->method('create')
            ->willReturn($this->productCollection);

        $this->block = $this->objectManager->getObject(
            NewWidget::class,
            [
                'context' => $this->context,
                'productCollectionFactory' => $productCollectionFactory
            ]
        );

        if (null === $productsPerPage) {
            $this->block->unsetData('products_per_page');
        } else {
            $this->block->setData('products_per_page', $productsPerPage);
        }

        $this->block->setData('show_pager', $pagerEnable);
        $this->block->setData('display_type', $displayType);
        $this->block->setProductsCount($productsCount);
        $this->block->toHtml();
    }

    /**
     * Test protected `_getProductCollection` and `getPageSize` methods via public `toHtml` method,
     * for display_type == DISPLAY_TYPE_NEW_PRODUCTS.
     *
     * @covers \Magento\Catalog\Block\Product\Widget\NewWidget::_getProductCollection
     * @param bool $pagerEnable
     * @param int $productsCount
     * @param int|null $productsPerPage
     * @param int $expectedPageSize
     */
    #[DataProvider('getProductCollectionDataProvider')]
    public function testGetProductNewCollection($pagerEnable, $productsCount, $productsPerPage, $expectedPageSize)
    {
        $this->generalGetProductCollection();

        $this->productCollection->expects($this->exactly(2))->method('setPageSize')
            ->willReturnCallback(
                function ($arg1) use ($productsCount, $expectedPageSize) {
                    if ($arg1 == $productsCount || $arg1 == $expectedPageSize) {
                        return $this->productCollection;
                    }
                }
            );

        $this->startTestGetProductCollection(
            NewWidget::DISPLAY_TYPE_NEW_PRODUCTS,
            $pagerEnable,
            $productsCount,
            $productsPerPage
        );
    }

    /**
     * Test protected `_getProductCollection` and `getPageSize` methods via public `toHtml` method,
     * for display_type == DISPLAY_TYPE_ALL_PRODUCTS.
     *
     * @covers \Magento\Catalog\Block\Product\Widget\NewWidget::_getProductCollection
     * @param bool $pagerEnable
     * @param int $productsCount
     * @param int|null $productsPerPage
     * @param int $expectedPageSize
     */
    #[DataProvider('getProductCollectionDataProvider')]
    public function testGetProductAllCollection($pagerEnable, $productsCount, $productsPerPage, $expectedPageSize)
    {
        $this->generalGetProductCollection();

        $this->productCollection->expects($this->atLeastOnce())->method('setPageSize')->with($expectedPageSize)
            ->willReturnSelf();

        $this->startTestGetProductCollection(
            NewWidget::DISPLAY_TYPE_ALL_PRODUCTS,
            $pagerEnable,
            $productsCount,
            $productsPerPage
        );
    }

    /**
     * Data provider for testGetProductNewCollection() and testGetProductAllCollection()
     *
     * @return array
     */
    public static function getProductCollectionDataProvider(): array
    {
        return [
            'pager_enabled_count_1_no_custom_per_page' => [
                'pagerEnable'      => true,
                'productsCount'    => 1,
                'productsPerPage'  => null,
                'expectedPageSize' => 5,
            ],
            'pager_enabled_count_5_no_custom_per_page' => [
                'pagerEnable'      => true,
                'productsCount'    => 5,
                'productsPerPage'  => null,
                'expectedPageSize' => 5,
            ],
            'pager_enabled_count_10_no_custom_per_page' => [
                'pagerEnable'      => true,
                'productsCount'    => 10,
                'productsPerPage'  => null,
                'expectedPageSize' => 5,
            ],

            'pager_enabled_custom_per_page_2' => [
                'pagerEnable'      => true,
                'productsCount'    => 1,
                'productsPerPage'  => 2,
                'expectedPageSize' => 2,
            ],
            'pager_enabled_custom_per_page_3' => [
                'pagerEnable'      => true,
                'productsCount'    => 5,
                'productsPerPage'  => 3,
                'expectedPageSize' => 3,
            ],
            'pager_enabled_custom_per_page_7' => [
                'pagerEnable'      => true,
                'productsCount'    => 10,
                'productsPerPage'  => 7,
                'expectedPageSize' => 7,
            ],

            'pager_disabled_count_1_no_custom_per_page' => [
                'pagerEnable'      => false,
                'productsCount'    => 1,
                'productsPerPage'  => null,
                'expectedPageSize' => 1,
            ],
            'pager_disabled_count_3_no_custom_per_page' => [
                'pagerEnable'      => false,
                'productsCount'    => 3,
                'productsPerPage'  => null,
                'expectedPageSize' => 3,
            ],
            'pager_disabled_count_5_no_custom_per_page' => [
                'pagerEnable'      => false,
                'productsCount'    => 5,
                'productsPerPage'  => null,
                'expectedPageSize' => 5,
            ],

            'pager_disabled_with_custom_per_page_3' => [
                'pagerEnable'      => false,
                'productsCount'    => 1,
                'productsPerPage'  => 3,
                'expectedPageSize' => 1,
            ],
            'pager_disabled_with_custom_per_page_5' => [
                'pagerEnable'      => false,
                'productsCount'    => 3,
                'productsPerPage'  => 5,
                'expectedPageSize' => 3,
            ],
            'pager_disabled_with_custom_per_page_10' => [
                'pagerEnable'      => false,
                'productsCount'    => 5,
                'productsPerPage'  => 10,
                'expectedPageSize' => 5,
            ],
        ];
    }

    /**
     * Unit test for getPagerHtml()
     *
     * @covers \Magento\Catalog\Block\Product\Widget\NewWidget::getPagerHtml()
     * @return void
     */
    public function testGetPagerHtml(): void
    {
        $pagerMock = $this->getMockBuilder(Pager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setShowPerPage', 'setPageVarName', 'setLimit', 'setCollection', 'toHtml'])
            ->addMethods(['setUseContainer', 'setShowAmounts', 'setTotalLimit'])
            ->getMock();

        $pagerMock->expects($this->once())
            ->method('setUseContainer')
            ->with(true)
            ->willReturnSelf();
        $pagerMock->expects($this->once())
            ->method('setShowAmounts')
            ->with(true)
            ->willReturnSelf();
        $pagerMock->expects($this->once())
            ->method('setShowPerPage')
            ->with(false)
            ->willReturnSelf();
        $pagerMock->expects($this->once())
            ->method('setPageVarName')
            ->with($this->block->getData('page_var_name'))
            ->willReturnSelf();
        $pagerMock->expects($this->once())
            ->method('setLimit')
            ->with(5)
            ->willReturnSelf();
        $pagerMock->expects($this->once())
            ->method('setTotalLimit')
            ->with(10)
            ->willReturnSelf();
        $pagerMock->expects($this->once())
            ->method('setCollection')
            ->with($this->block->getProductCollection())
            ->willReturnSelf();
        $pagerMock->expects($this->once())
            ->method('toHtml')
            ->willReturn('pager-html');
        $this->layout->expects($this->once())
            ->method('createBlock')
            ->with(Pager::class, 'widget.new.product.list.pager')
            ->willReturn($pagerMock);

        $this->block->setData('show_pager', true);
        $this->assertSame('pager-html', $this->block->getPagerHtml());
    }

    /**
     * Unit test for getPagerHtml() when show_pager is false
     *
     * @covers \Magento\Catalog\Block\Product\Widget\NewWidget::getPagerHtml()
     * @return void
     */
    public function testGetPagerHtmlWhenShowPagerFalse(): void
    {
        $this->block->setData('show_pager', false);
        $this->assertEmpty($this->block->getPagerHtml());
    }

    /**
     * Unit test for getCacheKeyInfo()
     *
     * @covers \Magento\Catalog\Block\Product\Widget\NewWidget::getCacheKeyInfo()
     * @return void
     */
    public function testGetCacheKeyInfo(): void
    {
        $serializer = $this->createMock(Json::class);
        $httpContext = $this->createMock(Context::class);
        $currency = $this->createMock(Currency::class);
        $store = $this->createMock(Store::class);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $theme = $this->createMock(ThemeInterface::class);
        $theme->method('getId')->willReturn('theme-id');
        $design = $this->createMock(DesignInterface::class);

        $serializer->expects($this->once())->method('serialize')
            ->with(['foo' => 'bar'])
            ->willReturn('serialized-params');
        $httpContext->method('getValue')->willReturnMap([
            [\Magento\Customer\Model\Context::CONTEXT_GROUP, null],
            [Context::CONTEXT_CURRENCY, null]
        ]);
        $currency->method('getCode')->willReturn('USD');
        $store->method('getDefaultCurrency')->willReturn($currency);
        $store->method('getId')->willReturn(1);
        $storeManager->method('getStore')->willReturn($store);
        $design->method('getDesignTheme')->willReturn($theme);

        $this->requestMock->expects($this->once())->method('getParam')->with('page_number', 1)->willReturn(2);
        $this->requestMock->expects($this->once())->method('getParams')->willReturn(['foo' => 'bar']);

        $block = $this->objectManager->getObject(
            NewWidget::class,
            [
                'context' => $this->context,
                'httpContext' => $httpContext,
                'serializer' => $serializer
            ]
        );

        $ref = new ReflectionClass($block);
        $prop = $ref->getProperty('_storeManager');
        $prop->setAccessible(true);
        $prop->setValue($block, $storeManager);
        $prop = $ref->getProperty('_design');
        $prop->setAccessible(true);
        $prop->setValue($block, $design);

        $block->setData('page_var_name', 'page_number');

        $result = $block->getCacheKeyInfo();

        $this->assertIsArray($result);
        $this->assertContains(NewWidget::DISPLAY_TYPE_ALL_PRODUCTS, $result);
        $this->assertContains(5, $result);
        $this->assertContains(2, $result);
        $this->assertContains('serialized-params', $result);
        $this->assertContains('USD', $result);
    }

    /**
     * Unit test for getProductsPerPage() using data provider
     *
     * @covers \Magento\Catalog\Block\Product\Widget\NewWidget::getProductsPerPage()
     * @dataProvider getProductsPerPageDataProvider
     * @param int|null $productsPerPage
     * @param int $expectedResult
     * @return void
     */
    public function testGetProductsPerPageWithDataProvider(?int $productsPerPage, int $expectedResult): void
    {
        if ($productsPerPage === null) {
            $this->block->unsetData('products_per_page');
        } else {
            $this->block->setData('products_per_page', $productsPerPage);
        }

        $this->assertSame($expectedResult, $this->block->getProductsPerPage());
    }

    /**
     * Data provider for testGetProductsPerPageWithDataProvider()
     *
     * @return array
     */
    public static function getProductsPerPageDataProvider(): array
    {
        return [
            'default_when_not_set' => [
                'productsPerPage' => null,
                'expectedResult' => NewWidget::DEFAULT_PRODUCTS_PER_PAGE
            ],
            'custom_positive_value' => [
                'productsPerPage' => 7,
                'expectedResult' => 7
            ],
            'explicit_zero_value' => [
                'productsPerPage' => 0,
                'expectedResult' => 0
            ]
        ];
    }
}
