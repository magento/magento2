<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Helper;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Object;
use Magento\Sales\Model\Quote\Item;

class CartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var Cart
     */
    protected $helper;

    protected function setUp()
    {
        $this->urlBuilderMock = $this->getMock('Magento\Framework\UrlInterface');
        $this->urlEncoder = $this->getMockBuilder('Magento\Framework\Url\EncoderInterface')->getMock();
        $this->urlEncoder->expects($this->any())
            ->method('encode')
            ->willReturnCallback(function ($url) {
                return strtr(base64_encode($url), '+/=', '-_,');
            });
        $this->requestMock = $this->getMock(
            '\Magento\Framework\App\RequestInterface',
            [
                'getRouteName',
                'getControllerName',
                'getParam',
                'setActionName',
                'getActionName',
                'setModuleName',
                'getModuleName',
                'getCookie'
            ]
        );
        $contextMock = $this->getMock('\Magento\Framework\App\Helper\Context', [], [], '', false);
        $contextMock->expects($this->any())->method('getUrlBuilder')->will($this->returnValue($this->urlBuilderMock));
        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())->method('getUrlEncoder')->will($this->returnValue($this->urlEncoder));
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->coreHelperMock = $this->getMock('\Magento\Core\Helper\Data', [], [], '', false);
        $this->scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->cartMock = $this->getMock('\Magento\Checkout\Model\Cart', [], [], '', false);
        $this->checkoutSessionMock = $this->getMock('\Magento\Checkout\Model\Session', [], [], '', false);

        $this->helper = new Cart(
            $contextMock,
            $this->storeManagerMock,
            $this->scopeConfigMock,
            $this->cartMock,
            $this->checkoutSessionMock
        );
    }

    public function testGetCart()
    {
        $this->assertEquals($this->cartMock, $this->helper->getCart());
    }

    public function testGetRemoveUrl()
    {
        $quoteItemId = 1;
        $quoteItemMock = $this->getMock('\Magento\Sales\Model\Quote\Item', [], [], '', false);
        $quoteItemMock->expects($this->any())->method('getId')->will($this->returnValue($quoteItemId));
        $currentUrl = 'http://www.example.com/';
        $this->urlBuilderMock->expects($this->any())->method('getCurrentUrl')->will($this->returnValue($currentUrl));
        $params = [
            'id' => $quoteItemId,
            Action::PARAM_NAME_BASE64_URL => strtr(base64_encode($currentUrl), '+/=', '-_,'),
        ];
        $this->urlBuilderMock->expects($this->once())->method('getUrl')->with('checkout/cart/delete', $params);
        $this->helper->getRemoveUrl($quoteItemMock);
    }

    public function testGetCartUrl()
    {
        $this->urlBuilderMock->expects($this->once())->method('getUrl')->with('checkout/cart', []);
        $this->helper->getCartUrl();
    }

    public function testGetQuote()
    {
        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->assertEquals($quoteMock, $this->helper->getQuote());
    }

    public function testGetItemsCount()
    {
        $itemsCount = 1;
        $this->cartMock->expects($this->any())->method('getItemsCount')->will($this->returnValue($itemsCount));
        $this->assertEquals($itemsCount, $this->helper->getItemsCount());
    }

    public function testGetItemsQty()
    {
        $itemsQty = 1;
        $this->cartMock->expects($this->any())->method('getItemsQty')->will($this->returnValue($itemsQty));
        $this->assertEquals($itemsQty, $this->helper->getItemsQty());
    }

    public function testGetSummaryCount()
    {
        $summaryQty = 1;
        $this->cartMock->expects($this->any())->method('getSummaryQty')->will($this->returnValue($summaryQty));
        $this->assertEquals($summaryQty, $this->helper->getSummaryCount());
    }

    public function testGetIsVirtualQuote()
    {
        $isVirtual = true;
        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->any())->method('isVirtual')->will($this->returnValue($isVirtual));
        $this->assertEquals($isVirtual, $this->helper->getIsVirtualQuote());
    }

    public function testGetShouldRedirectToCart()
    {
        $storeId = 1;
        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')
            ->with(Cart::XML_PATH_REDIRECT_TO_CART, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)
            ->will($this->returnValue(true));
        $this->assertTrue($this->helper->getShouldRedirectToCart($storeId));
    }

    public function testGetAddUrl()
    {
        $productEntityId = 1;
        $storeId = 1;
        $productMock = $this->getMock('\Magento\Catalog\Model\Product',
            ['getEntityId', 'hasUrlDataObject', 'getUrlDataObject', '__wakeup'], [], '', false);
        $productMock->expects($this->any())->method('getEntityId')->will($this->returnValue($productEntityId));
        $productMock->expects($this->any())->method('hasUrlDataObject')->will($this->returnValue(true));
        $productMock->expects($this->any())->method('getUrlDataObject')
            ->will($this->returnValue(new Object(['store_id' => $storeId])));

        $currentUrl = 'http://www.example.com/';
        $this->urlBuilderMock->expects($this->any())->method('getCurrentUrl')->will($this->returnValue($currentUrl));

        $this->requestMock->expects($this->any())->method('getRouteName')->will($this->returnValue('checkout'));
        $this->requestMock->expects($this->any())->method('getControllerName')->will($this->returnValue('cart'));

        $params = [
            Action::PARAM_NAME_URL_ENCODED => strtr(base64_encode($currentUrl), '+/=', '-_,'),
            'product' => $productEntityId,
            'custom_param' => 'value',
            '_scope' => $storeId,
            '_scope_to_url' => true,
            'in_cart' => 1,
        ];

        $this->urlBuilderMock->expects($this->once())->method('getUrl')->with('checkout/cart/add', $params);
        $this->helper->getAddUrl($productMock, ['custom_param' => 'value']);
    }

    /**
     * @param integer $id
     * @param string $url
     * @param bool $isAjax
     * @param string $expectedPostData
     *
     * @dataProvider deletePostJsonDataProvider
     */
    public function testGetDeletePostJson($id, $url, $isAjax, $expectedPostData)
    {
        $storeManager = $this->getMockForAbstractClass('\Magento\Store\Model\StoreManagerInterface');
        $coreData = $this->getMock('\Magento\Core\Helper\Data', [], [], '', false);
        $scopeConfig = $this->getMockForAbstractClass('\Magento\Framework\App\Config\ScopeConfigInterface');
        $checkoutCart = $this->getMock('\Magento\Checkout\Model\Cart', [], [], '', false);
        $checkoutSession = $this->getMock('\Magento\Checkout\Model\Session', [], [], '', false);

        $context = $this->getMock('\Magento\Framework\App\Helper\Context', [], [], '', false);
        $urlBuilder = $this->getMock('Magento\Framework\UrlInterface');
        $context->expects($this->once())
            ->method('getUrlBuilder')
            ->will($this->returnValue($urlBuilder));
        $context->expects($this->any())
            ->method('getUrlEncoder')
            ->willReturn($this->urlEncoder);

        $item = $this->getMock('Magento\Sales\Model\Quote\Item', [], [], '', false);
        $request = $this->getMock('\Magento\Framework\App\Request\Http', [], [], '', false);
        $context->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $helper = new Cart(
            $context,
            $storeManager,
            $scopeConfig,
            $checkoutCart,
            $checkoutSession
        );

        $item->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($id));

        $request->expects($this->once())
            ->method('isAjax')
            ->will($this->returnValue($isAjax));

        $urlBuilder->expects($this->any())
            ->method('getCurrentUrl')
            ->will($this->returnValue($url));

        $urlBuilder->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue($url));

        $result = $helper->getDeletePostJson($item);
        $this->assertEquals($expectedPostData, $result);
    }

    /**
     * @return array
     */
    public function deletePostJsonDataProvider()
    {
        $url = 'http://localhost.com/dev/checkout/cart/delete/';
        $uenc = strtr(base64_encode($url), '+/=', '-_,');
        $id = 1;
        $expectedPostData1 = json_encode(
            [
                'action' => $url,
                'data' => ['id' => $id, 'uenc' => $uenc],
            ]
        );
        $expectedPostData2 = json_encode(
            [
                'action' => $url,
                'data' => ['id' => $id],
            ]
        );

        return [
            [$id, $url, false, $expectedPostData1],
            [$id, $url, true, $expectedPostData2],
        ];
    }
}
