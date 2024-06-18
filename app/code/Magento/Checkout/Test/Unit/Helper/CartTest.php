<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Helper;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\EncoderInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var MockObject
     */
    protected $cartMock;

    /**
     * @var MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var MockObject|EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var Cart
     */
    protected $helper;

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManager($this);
        /** @var Context $context */
        $context = $objectManagerHelper->getObject(
            Context::class,
            [
                'httpRequest' => $this->requestMock,
            ]
        );
        $className = Cart::class;
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
        $quoteItemMock = $this->createMock(Item::class);
        $quoteItemMock->expects($this->any())->method('getId')->willReturn($quoteItemId);
        $currentUrl = 'http://www.example.com/';
        $this->urlBuilderMock->expects($this->any())->method('getCurrentUrl')->willReturn($currentUrl);
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
        $quoteMock = $this->createMock(Quote::class);
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $this->assertEquals($quoteMock, $this->helper->getQuote());
    }

    public function testGetItemsCount()
    {
        $itemsCount = 1;
        $this->cartMock->expects($this->any())->method('getItemsCount')->willReturn($itemsCount);
        $this->assertEquals($itemsCount, $this->helper->getItemsCount());
    }

    public function testGetItemsQty()
    {
        $itemsQty = 1;
        $this->cartMock->expects($this->any())->method('getItemsQty')->willReturn($itemsQty);
        $this->assertEquals($itemsQty, $this->helper->getItemsQty());
    }

    public function testGetSummaryCount()
    {
        $summaryQty = 1;
        $this->cartMock->expects($this->any())->method('getSummaryQty')->willReturn($summaryQty);
        $this->assertEquals($summaryQty, $this->helper->getSummaryCount());
    }

    public function testAddUrlWithUencPlaceholder()
    {
        $productEntityId = 1;
        $storeId = 1;
        $isRequestSecure = false;
        $productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['hasUrlDataObject', 'getUrlDataObject'])
            ->onlyMethods(['getEntityId'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->any())->method('getEntityId')->willReturn($productEntityId);
        $productMock->expects($this->any())->method('hasUrlDataObject')->willReturn(true);
        $productMock->expects($this->any())->method('getUrlDataObject')
            ->willReturn(new DataObject(['store_id' => $storeId]));

        $this->requestMock->expects($this->any())->method('getRouteName')->willReturn('checkout');
        $this->requestMock->expects($this->any())->method('getControllerName')->willReturn('cart');
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
        $quoteMock = $this->createMock(Quote::class);
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->any())->method('isVirtual')->willReturn($isVirtual);
        $this->assertEquals($isVirtual, $this->helper->getIsVirtualQuote());
    }

    public function testGetShouldRedirectToCart()
    {
        $storeId = 1;
        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')
            ->with(Cart::XML_PATH_REDIRECT_TO_CART, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn(true);
        $this->assertTrue($this->helper->getShouldRedirectToCart($storeId));
    }

    public function testGetAddUrl()
    {
        $productEntityId = 1;
        $storeId = 1;
        $isRequestSecure = false;
        $productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['hasUrlDataObject', 'getUrlDataObject'])
            ->onlyMethods(['getEntityId'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->any())->method('getEntityId')->willReturn($productEntityId);
        $productMock->expects($this->any())->method('hasUrlDataObject')->willReturn(true);
        $productMock->expects($this->any())->method('getUrlDataObject')
            ->willReturn(new DataObject(['store_id' => $storeId]));

        $currentUrl = 'http://www.example.com/';
        $this->urlBuilderMock->expects($this->any())->method('getCurrentUrl')->willReturn($currentUrl);

        $this->requestMock->expects($this->any())->method('getRouteName')->willReturn('checkout');
        $this->requestMock->expects($this->any())->method('getControllerName')->willReturn('cart');
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
        $item = $this->createMock(Item::class);

        $item->expects($this->once())
            ->method('getId')
            ->willReturn($id);

        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn($isAjax);

        $this->urlBuilderMock->expects($this->any())
            ->method('getCurrentUrl')
            ->willReturn($url);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($url);

        $result = $this->helper->getDeletePostJson($item);
        $this->assertEquals($expectedPostData, $result);
    }

    /**
     * @return array
     */
    public static function deletePostJsonDataProvider()
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
