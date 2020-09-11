<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Cart;

use Magento\Catalog\Helper\Image;
use Magento\Checkout\Block\Cart\Sidebar;
use Magento\Checkout\Block\Shipping\Price;
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
    /** @var ObjectManager  */
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

    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->layoutMock = $this->createMock(Layout::class);
        $this->checkoutSessionMock = $this->createMock(Session::class);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->imageHelper = $this->createMock(Image::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

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
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->serializer = $this->createMock(Json::class);

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

    public function testGetTotalsHtml()
    {
        $totalsHtml = "$134.36";
        $totalsBlockMock = $this->getMockBuilder(Price::class)
            ->disableOriginalConstructor()
            ->setMethods(['toHtml'])
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

    public function testGetConfig()
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

        $this->requestMock->expects($this->any())
            ->method('isSecure')
            ->willReturn(false);

        $this->urlBuilderMock->expects($this->exactly(4))
            ->method('getUrl')
            ->willReturnMap($valueMap);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getBaseUrl')->willReturn($baseUrl);

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(
                Sidebar::XML_PATH_CHECKOUT_SIDEBAR_COUNT,
                ScopeInterface::SCOPE_STORE
            )->willReturn(3);

        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(
                'checkout/sidebar/max_items_display_count',
                ScopeInterface::SCOPE_STORE
            )->willReturn(8);

        $storeMock->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);

        $this->assertEquals($expectedResult, $this->model->getConfig());
    }

    public function testGetIsNeedToDisplaySideBar()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Sidebar::XML_PATH_CHECKOUT_SIDEBAR_DISPLAY,
                ScopeInterface::SCOPE_STORE
            )->willReturn(true);

        $this->assertTrue($this->model->getIsNeedToDisplaySideBar());
    }

    public function testGetTotalsCache()
    {
        $quoteMock = $this->createMock(Quote::class);
        $totalsMock = ['totals'];
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getTotals')->willReturn($totalsMock);

        $this->assertEquals($totalsMock, $this->model->getTotalsCache());
    }
}
