<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Checkout\Test\Unit\Helper;

use \Magento\Checkout\Helper\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Item;

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
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Framework\App\Helper\Context $context */
        $context = $objectManagerHelper->getObject(
            \Magento\Framework\App\Helper\Context::class,
            [
                'httpRequest' => $this->requestMock,
            ]
        );
        $className = \Magento\Checkout\Helper\Cart::class;
        $arguments = $objectManagerHelper->getConstructArguments($className, ['context' => $context]);
        $this->urlBuilderMock = $context->getUrlBuilder();
        $this->urlEncoder = $context->getUrlEncoder();
        $this->urlEncoder->expects($this->any())
            ->method('encode')
            ->willReturnCallback(function ($url) {
                return strtr(base64_encode($url), '+/=', '-_,');
            });
        $this->scopeConfigMock = $context->getScopeConfig();
        $this->cartMock = $arguments['checkoutCart'];
        $this->checkoutSessionMock = $arguments['checkoutSession'];

        $this->helper = $objectManagerHelper->getObject($className, $arguments);
    }

    public function testGetCart()
    {
        $this->assertEquals($this->cartMock, $this->helper->getCart());
    }

    public function testGetRemoveUrl()
    {
        $quoteItemId = 1;
        $quoteItemMock = $this->getMock(\Magento\Quote\Model\Quote\Item::class, [], [], '', false);
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
        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
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

    public function testAddUrlWithUencPlaceholder()
    {
        $productEntityId = 1;
        $storeId = 1;
        $isRequestSecure = false;
        $productMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getEntityId', 'hasUrlDataObject', 'getUrlDataObject', '__wakeup'], [], '', false);
        $productMock->expects($this->any())->method('getEntityId')->will($this->returnValue($productEntityId));
        $productMock->expects($this->any())->method('hasUrlDataObject')->will($this->returnValue(true));
        $productMock->expects($this->any())->method('getUrlDataObject')
            ->will($this->returnValue(new DataObject(['store_id' => $storeId])));

        $this->requestMock->expects($this->any())->method('getRouteName')->will($this->returnValue('checkout'));
        $this->requestMock->expects($this->any())->method('getControllerName')->will($this->returnValue('cart'));
        $this->requestMock->expects($this->once())->method('isSecure')->willReturn($isRequestSecure);

        $params = [
            Action::PARAM_NAME_URL_ENCODED => strtr("%uenc%", '+/=', '-_,'),
            'product' => $productEntityId,
            'custom_param' => 'value',
            '_scope' => $storeId,
            '_scope_to_url' => true,
            'in_cart' => 1,
            '_secure' => $isRequestSecure
        ];

        $this->urlBuilderMock->expects($this->once())->method('getUrl')->with('checkout/cart/add', $params);
        $this->helper->getAddUrl($productMock, ['custom_param' => 'value', 'useUencPlaceholder' => 1]);
    }

    public function testGetIsVirtualQuote()
    {
        $isVirtual = true;
        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
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
        $isRequestSecure = false;
        $productMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getEntityId', 'hasUrlDataObject', 'getUrlDataObject', '__wakeup'], [], '', false);
        $productMock->expects($this->any())->method('getEntityId')->will($this->returnValue($productEntityId));
        $productMock->expects($this->any())->method('hasUrlDataObject')->will($this->returnValue(true));
        $productMock->expects($this->any())->method('getUrlDataObject')
            ->will($this->returnValue(new DataObject(['store_id' => $storeId])));

        $currentUrl = 'http://www.example.com/';
        $this->urlBuilderMock->expects($this->any())->method('getCurrentUrl')->will($this->returnValue($currentUrl));

        $this->requestMock->expects($this->any())->method('getRouteName')->will($this->returnValue('checkout'));
        $this->requestMock->expects($this->any())->method('getControllerName')->will($this->returnValue('cart'));
        $this->requestMock->expects($this->once())->method('isSecure')->willReturn($isRequestSecure);

        $params = [
            Action::PARAM_NAME_URL_ENCODED => strtr(base64_encode($currentUrl), '+/=', '-_,'),
            'product' => $productEntityId,
            'custom_param' => 'value',
            '_scope' => $storeId,
            '_scope_to_url' => true,
            'in_cart' => 1,
            '_secure' => $isRequestSecure
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
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testGetDeletePostJson($id, $url, $isAjax, $expectedPostData)
    {
        $item = $this->getMock(\Magento\Quote\Model\Quote\Item::class, [], [], '', false);

        $item->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($id));

        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->will($this->returnValue($isAjax));

        $this->urlBuilderMock->expects($this->any())
            ->method('getCurrentUrl')
            ->will($this->returnValue($url));

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue($url));

        $result = $this->helper->getDeletePostJson($item);
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
