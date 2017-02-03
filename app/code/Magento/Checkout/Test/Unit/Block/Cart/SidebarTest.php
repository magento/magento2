<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart;

class SidebarTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager  */
    protected $_objectManager;

    /**
     * @var \Magento\Checkout\Block\Cart\Sidebar
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->requestMock = $this->getMock('\Magento\Framework\App\RequestInterface');
        $this->layoutMock = $this->getMock('\Magento\Framework\View\Layout', [], [], '', false);
        $this->checkoutSessionMock = $this->getMock('\Magento\Checkout\Model\Session', [], [], '', false);
        $this->urlBuilderMock = $this->getMock('\Magento\Framework\UrlInterface', [], [], '', false);
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface', [], [], '', false);
        $this->imageHelper = $this->getMock('Magento\Catalog\Helper\Image', [], [], '', false);
        $this->scopeConfigMock = $this->getMock(
            '\Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            [],
            '',
            false
        );

        $contextMock = $this->getMock(
            '\Magento\Framework\View\Element\Template\Context',
            ['getLayout', 'getUrlBuilder', 'getStoreManager', 'getScopeConfig', 'getRequest'],
            [],
            '',
            false
        );
        $contextMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($this->layoutMock));
        $contextMock->expects($this->once())
            ->method('getUrlBuilder')
            ->will($this->returnValue($this->urlBuilderMock));
        $contextMock->expects($this->once())
            ->method('getStoreManager')
            ->will($this->returnValue($this->storeManagerMock));
        $contextMock->expects($this->once())
            ->method('getScopeConfig')
            ->will($this->returnValue($this->scopeConfigMock));
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));

        $this->model = $this->_objectManager->getObject(
            'Magento\Checkout\Block\Cart\Sidebar',
            [
                'context' => $contextMock,
                'imageHelper' => $this->imageHelper,
                'checkoutSession' => $this->checkoutSessionMock
            ]
        );
    }

    public function testGetTotalsHtml()
    {
        $totalsHtml = "$134.36";
        $totalsBlockMock = $this->getMockBuilder('\Magento\Checkout\Block\Shipping\Price')
            ->disableOriginalConstructor()
            ->setMethods(['toHtml'])
            ->getMock();

        $totalsBlockMock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($totalsHtml));

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('checkout.cart.minicart.totals')
            ->will($this->returnValue($totalsBlockMock));

        $this->assertEquals($totalsHtml, $this->model->getTotalsHtml());
    }

    public function testGetConfig()
    {
        $storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);

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
            'minicartMaxItemsVisible' => 3
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
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getBaseUrl')->willReturn($baseUrl);
        $this->imageHelper->expects($this->once())->method('getFrame')->willReturn(false);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'checkout/sidebar/count',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )->willReturn(3);

        $this->assertEquals($expectedResult, $this->model->getConfig());
    }

    public function testGetIsNeedToDisplaySideBar()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Checkout\Block\Cart\Sidebar::XML_PATH_CHECKOUT_SIDEBAR_DISPLAY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )->willReturn(true);

        $this->assertTrue($this->model->getIsNeedToDisplaySideBar());
    }

    public function testGetTotalsCache()
    {
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $totalsMock = ['totals'];
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getTotals')->willReturn($totalsMock);

        $this->assertEquals($totalsMock, $this->model->getTotalsCache());
    }
}
