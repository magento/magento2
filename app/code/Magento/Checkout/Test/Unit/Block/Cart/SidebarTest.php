<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Cart;

use Magento\Catalog\Helper\Image;
use Magento\Checkout\Block\Cart\Sidebar;
use Magento\Checkout\Block\Shipping\Price;
use Magento\Framework\App\CacheInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Layout;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SidebarTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Sidebar
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $layoutMock;

    /**
     * @var MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $imageHelper;

    /**
     * @var MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var MockObject
     */
    private $serializer;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);

        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->layoutMock = $this->createMock(Layout::class);
        $this->checkoutSessionMock = $this->createMock(Session::class);
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->imageHelper = $this->createMock(Image::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $contextMock = $this->createPartialMock(
            Context::class,
            ['getLayout', 'getUrlBuilder', 'getStoreManager', 'getScopeConfig', 'getRequest']
        );
        $contextMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($this->layoutMock);
        $contextMock->expects($this->once())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);
        $contextMock->expects($this->once())
            ->method('getStoreManager')
            ->willReturn($this->storeManagerMock);
        $contextMock->expects($this->once())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);
        $contextMock->method('getRequest')->willReturn($this->requestMock);

        $this->serializer = $this->createMock(Json::class);

        $cacheMock = $this->createMock(CacheInterface::class);
        $this->_objectManager->prepareObjectManager([
            [CacheInterface::class, $cacheMock]
        ]);

        $this->model = $this->_objectManager->getObject(
            Sidebar::class,
            [
                'context' => $contextMock,
                'imageHelper' => $this->imageHelper,
                'checkoutSession' => $this->checkoutSessionMock,
                'serializer' => $this->serializer
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetTotalsHtml(): void
    {
        $totalsHtml = "$134.36";
        $totalsBlockMock = $this->getMockBuilder(Price::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toHtml'])
            ->getMock();

        $totalsBlockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($totalsHtml);

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('checkout.cart.minicart.totals')
            ->willReturn($totalsBlockMock);

        $this->assertEquals($totalsHtml, $this->model->getTotalsHtml());
    }

    /**
     * @return void
     */
    public function testGetConfig(): void
    {
        $websiteId = 100;
        $storeMock = $this->createMock(Store::class);

        $shoppingCartUrl = 'http://url.com/cart';
        $checkoutUrl = 'http://url.com/checkout';
        $updateItemQtyUrl = 'http://url.com/updateItemQty';
        $removeItemUrl = 'http://url.com/removeItem';
        $baseUrl = 'http://url.com/';
        $imageTemplate = 'Magento_Catalog/product/image_with_borders';

        $expectedResult = [
            'shoppingCartUrl' => $shoppingCartUrl,
            'checkoutUrl' => $checkoutUrl,
            'updateItemQtyUrl' => $updateItemQtyUrl,
            'removeItemUrl' => $removeItemUrl,
            'imageTemplate' => $imageTemplate,
            'baseUrl' => $baseUrl,
            'minicartMaxItemsVisible' => 3,
            'websiteId' => 100,
            'maxItemsToDisplay' => 8,
            'storeId' => null,
            'storeGroupId' => null
        ];

        $valueMap = [
            ['checkout/cart', [], $shoppingCartUrl],
            ['checkout', [], $checkoutUrl],
            ['checkout/sidebar/updateItemQty', ['_secure' => false], $updateItemQtyUrl],
            ['checkout/sidebar/removeItem', ['_secure' => false], $removeItemUrl]
        ];

        $this->requestMock->method('isSecure')->willReturn(false);

        $this->urlBuilderMock->expects($this->exactly(4))
            ->method('getUrl')
            ->willReturnMap($valueMap);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getBaseUrl')->willReturn($baseUrl);

        $this->scopeConfigMock
            ->method('getValue')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1 === Sidebar::XML_PATH_CHECKOUT_SIDEBAR_COUNT &&
                    $arg2 === ScopeInterface::SCOPE_STORE) {
                    return 3;
                } elseif ($arg1 === 'checkout/sidebar/max_items_display_count' &&
                    $arg2 === ScopeInterface::SCOPE_STORE) {
                    return 8;
                }
            });

        $storeMock->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);

        $this->assertEquals($expectedResult, $this->model->getConfig());
    }

    /**
     * @return void
     */
    public function testGetIsNeedToDisplaySideBar(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Sidebar::XML_PATH_CHECKOUT_SIDEBAR_DISPLAY,
                ScopeInterface::SCOPE_STORE
            )->willReturn(true);

        $this->assertTrue($this->model->getIsNeedToDisplaySideBar());
    }

    /**
     * @return void
     */
    public function testGetTotalsCache(): void
    {
        $quoteMock = $this->createMock(Quote::class);
        $totalsMock = ['totals'];
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getTotals')->willReturn($totalsMock);

        $this->assertEquals($totalsMock, $this->model->getTotalsCache());
    }

    /**
     * @return void
     */
    public function testGetJsLayoutWithNoSecondaryMinicart(): void
    {
        $jsLayout = [
            'components' => [
                'minicart_content' => [
                    'component' => 'Magento_Checkout/js/view/minicart',
                    'config' => [
                        'itemRenderer' => ['default' => 'defaultRenderer']
                    ],
                    'children' => [
                        'item.renderer' => ['component' => 'uiComponent']
                    ]
                ]
            ]
        ];

        $reflection = new \ReflectionProperty($this->model, 'jsLayout');
        $reflection->setValue($this->model, $jsLayout);

        $this->serializer->method('serialize')
            ->willReturnCallback(function ($data) {
                return json_encode($data);
            });

        $result = json_decode($this->model->getJsLayout(), true);

        $this->assertArrayHasKey('minicart_content', $result['components']);
        $this->assertEquals(
            ['default' => 'defaultRenderer'],
            $result['components']['minicart_content']['config']['itemRenderer']
        );
    }

    /**
     * @return void
     */
    public function testGetJsLayoutCopiesRenderersToSecondaryMinicart(): void
    {
        $jsLayout = [
            'components' => [
                'minicart_content' => [
                    'component' => 'Magento_Checkout/js/view/minicart',
                    'config' => [
                        'itemRenderer' => ['default' => 'defaultRenderer']
                    ],
                    'children' => [
                        'item.renderer' => ['component' => 'uiComponent'],
                        'subtotal.container' => ['component' => 'uiComponent']
                    ]
                ],
                'minicart_content_footer' => [
                    'component' => 'Magento_Checkout/js/view/minicart',
                    'config' => [
                        'template' => 'Magento_Checkout/minicart/content'
                    ],
                    'children' => []
                ]
            ]
        ];

        $reflection = new \ReflectionProperty($this->model, 'jsLayout');
        $reflection->setValue($this->model, $jsLayout);

        $this->serializer->method('serialize')
            ->willReturnCallback(function ($data) {
                return json_encode($data);
            });

        $result = json_decode($this->model->getJsLayout(), true);

        $this->assertArrayHasKey('minicart_content_footer', $result['components']);
        $footer = $result['components']['minicart_content_footer'];
        $this->assertEquals(
            ['default' => 'defaultRenderer'],
            $footer['config']['itemRenderer']
        );
        $this->assertArrayHasKey('item.renderer', $footer['children']);
        $this->assertArrayHasKey('subtotal.container', $footer['children']);
    }

    /**
     * @return void
     */
    public function testGetJsLayoutDoesNotOverrideExistingConfig(): void
    {
        $jsLayout = [
            'components' => [
                'minicart_content' => [
                    'component' => 'Magento_Checkout/js/view/minicart',
                    'config' => [
                        'itemRenderer' => ['default' => 'defaultRenderer']
                    ],
                    'children' => [
                        'item.renderer' => ['component' => 'uiComponent']
                    ]
                ],
                'minicart_content_footer' => [
                    'component' => 'Magento_Checkout/js/view/minicart',
                    'config' => [
                        'itemRenderer' => ['default' => 'customRenderer']
                    ],
                    'children' => [
                        'item.renderer' => ['component' => 'customComponent']
                    ]
                ]
            ]
        ];

        $reflection = new \ReflectionProperty($this->model, 'jsLayout');
        $reflection->setValue($this->model, $jsLayout);

        $this->serializer->method('serialize')
            ->willReturnCallback(function ($data) {
                return json_encode($data);
            });

        $result = json_decode($this->model->getJsLayout(), true);

        $footer = $result['components']['minicart_content_footer'];
        $this->assertEquals(
            ['default' => 'customRenderer'],
            $footer['config']['itemRenderer']
        );
        $this->assertEquals(
            ['component' => 'customComponent'],
            $footer['children']['item.renderer']
        );
    }
}
