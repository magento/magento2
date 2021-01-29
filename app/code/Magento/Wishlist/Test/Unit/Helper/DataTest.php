<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Registry;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\Item as WishlistItem;
use Magento\Wishlist\Model\Wishlist;
use Magento\Customer\Model\Session;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \Magento\Wishlist\Helper\Data */
    protected $model;

    /** @var  WishlistProviderInterface |\PHPUnit\Framework\MockObject\MockObject */
    protected $wishlistProvider;

    /** @var  Registry |\PHPUnit\Framework\MockObject\MockObject */
    protected $coreRegistry;

    /** @var  PostHelper |\PHPUnit\Framework\MockObject\MockObject */
    protected $postDataHelper;

    /** @var  WishlistItem |\PHPUnit\Framework\MockObject\MockObject */
    protected $wishlistItem;

    /** @var  Product |\PHPUnit\Framework\MockObject\MockObject */
    protected $product;

    /** @var  StoreManagerInterface |\PHPUnit\Framework\MockObject\MockObject */
    protected $storeManager;

    /** @var  Store |\PHPUnit\Framework\MockObject\MockObject */
    protected $store;

    /** @var  UrlInterface |\PHPUnit\Framework\MockObject\MockObject */
    protected $urlBuilder;

    /** @var  Wishlist |\PHPUnit\Framework\MockObject\MockObject */
    protected $wishlist;

    /** @var  EncoderInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $urlEncoderMock;

    /** @var  RequestInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $requestMock;

    /** @var  Context |\PHPUnit\Framework\MockObject\MockObject */
    protected $context;

    /** @var  Session |\PHPUnit\Framework\MockObject\MockObject */
    protected $customerSession;

    /**
     * Set up mock objects for tested class
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);

        $this->urlEncoderMock = $this->getMockBuilder(\Magento\Framework\Url\EncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getServer'])
            ->getMockForAbstractClass();

        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(\Magento\Framework\App\Helper\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->once())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilder);
        $this->context->expects($this->once())
            ->method('getUrlEncoder')
            ->willReturn($this->urlEncoderMock);
        $this->context->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->wishlistProvider = $this->getMockBuilder(\Magento\Wishlist\Controller\WishlistProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->coreRegistry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->postDataHelper = $this->getMockBuilder(\Magento\Framework\Data\Helper\PostHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->wishlistItem = $this->getMockBuilder(\Magento\Wishlist\Model\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getProduct',
                    'getWishlistItemId',
                    'getQty',
                ]
            )->getMock();

        $this->wishlist = $this->getMockBuilder(\Magento\Wishlist\Model\Wishlist::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSession = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Wishlist\Helper\Data::class,
            [
                'context' => $this->context,
                'customerSession' => $this->customerSession,
                'storeManager' => $this->storeManager,
                'wishlistProvider' => $this->wishlistProvider,
                'coreRegistry' => $this->coreRegistry,
                'postDataHelper' => $this->postDataHelper
            ]
        );
    }

    public function testGetAddToCartUrl()
    {
        $url = 'http://magento.com/wishlist/index/index/wishlist_id/1/?___store=default';

        $this->store->expects($this->once())
            ->method('getUrl')
            ->with('wishlist/index/cart', ['item' => '%item%'])
            ->willReturn($url);

        $this->urlBuilder->expects($this->any())
            ->method('getUrl')
            ->with('wishlist/index/index', ['_current' => true, '_use_rewrite' => true, '_scope_to_url' => true])
            ->willReturn($url);

        $this->assertEquals($url, $this->model->getAddToCartUrl('%item%'));
    }

    public function testGetConfigureUrl()
    {
        $url = 'http://magento2ce/wishlist/index/configure/id/4/product_id/30/';

        /** @var \Magento\Wishlist\Model\Item|\PHPUnit\Framework\MockObject\MockObject $wishlistItem */
        $wishlistItem = $this->createPartialMock(
            \Magento\Wishlist\Model\Item::class,
            ['getWishlistItemId', 'getProductId']
        );
        $wishlistItem
            ->expects($this->once())
            ->method('getWishlistItemId')
            ->willReturn(4);
        $wishlistItem
            ->expects($this->once())
            ->method('getProductId')
            ->willReturn(30);

        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('wishlist/index/configure', ['id' => 4, 'product_id' => 30])
            ->willReturn($url);

        $this->assertEquals($url, $this->model->getConfigureUrl($wishlistItem));
    }

    public function testGetWishlist()
    {
        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn($this->wishlist);

        $this->assertEquals($this->wishlist, $this->model->getWishlist());
    }

    public function testGetWishlistWithCoreRegistry()
    {
        $this->coreRegistry->expects($this->any())
            ->method('registry')
            ->willReturn($this->wishlist);

        $this->assertEquals($this->wishlist, $this->model->getWishlist());
    }

    public function testGetAddToCartParams()
    {
        $url = 'result url';
        $storeId = 1;
        $wishlistItemId = 1;
        $wishlistItemQty = 1;

        $this->wishlistItem->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->product);
        $this->wishlistItem->expects($this->once())
            ->method('getWishlistItemId')
            ->willReturn($wishlistItemId);
        $this->wishlistItem->expects($this->once())
            ->method('getQty')
            ->willReturn($wishlistItemQty);

        $this->product->expects($this->once())
            ->method('isVisibleInSiteVisibility')
            ->willReturn(true);
        $this->product->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->requestMock->expects($this->never())
            ->method('getServer');

        $this->urlEncoderMock->expects($this->never())
            ->method('encode');

        $this->store->expects($this->once())
            ->method('getUrl')
            ->with('wishlist/index/cart')
            ->willReturn($url);

        $expected = [
            'item' => $wishlistItemId,
            'qty' => $wishlistItemQty,
            ActionInterface::PARAM_NAME_URL_ENCODED => '',
        ];
        $this->postDataHelper->expects($this->once())
            ->method('getPostData')
            ->with($url, $expected)
            ->willReturn($url);

        $this->assertEquals($url, $this->model->getAddToCartParams($this->wishlistItem));
    }

    public function testGetAddToCartParamsWithReferer()
    {
        $url = 'result url';
        $storeId = 1;
        $wishlistItemId = 1;
        $wishlistItemQty = 1;
        $referer = 'referer';
        $refererEncoded = 'referer_encoded';

        $this->wishlistItem->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->product);
        $this->wishlistItem->expects($this->once())
            ->method('getWishlistItemId')
            ->willReturn($wishlistItemId);
        $this->wishlistItem->expects($this->once())
            ->method('getQty')
            ->willReturn($wishlistItemQty);

        $this->product->expects($this->once())
            ->method('isVisibleInSiteVisibility')
            ->willReturn(true);
        $this->product->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->requestMock->expects($this->once())
            ->method('getServer')
            ->with('HTTP_REFERER')
            ->willReturn($referer);

        $this->urlEncoderMock->expects($this->once())
            ->method('encode')
            ->with($referer)
            ->willReturn($refererEncoded);

        $this->store->expects($this->once())
            ->method('getUrl')
            ->with('wishlist/index/cart')
            ->willReturn($url);

        $expected = [
            'item' => $wishlistItemId,
            ActionInterface::PARAM_NAME_URL_ENCODED => $refererEncoded,
            'qty' => $wishlistItemQty,
        ];
        $this->postDataHelper->expects($this->once())
            ->method('getPostData')
            ->with($url, $expected)
            ->willReturn($url);

        $this->assertEquals($url, $this->model->getAddToCartParams($this->wishlistItem, true));
    }

    public function testGetRemoveParams()
    {
        $url = 'result url';
        $wishlistItemId = 1;

        $this->wishlistItem->expects($this->once())
            ->method('getWishlistItemId')
            ->willReturn($wishlistItemId);

        $this->requestMock->expects($this->never())
            ->method('getServer');

        $this->urlEncoderMock->expects($this->never())
            ->method('encode');

        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('wishlist/index/remove', [])
            ->willReturn($url);

        $this->postDataHelper->expects($this->once())
            ->method('getPostData')
            ->with($url, ['item' => $wishlistItemId, ActionInterface::PARAM_NAME_URL_ENCODED => ''])
            ->willReturn($url);

        $this->assertEquals($url, $this->model->getRemoveParams($this->wishlistItem));
    }

    public function testGetRemoveParamsWithReferer()
    {
        $url = 'result url';
        $wishlistItemId = 1;
        $referer = 'referer';
        $refererEncoded = 'referer_encoded';

        $this->wishlistItem->expects($this->once())
            ->method('getWishlistItemId')
            ->willReturn($wishlistItemId);

        $this->requestMock->expects($this->once())
            ->method('getServer')
            ->with('HTTP_REFERER')
            ->willReturn($referer);

        $this->urlEncoderMock->expects($this->once())
            ->method('encode')
            ->with($referer)
            ->willReturn($refererEncoded);

        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('wishlist/index/remove', [])
            ->willReturn($url);

        $this->postDataHelper->expects($this->once())
            ->method('getPostData')
            ->with($url, ['item' => $wishlistItemId, ActionInterface::PARAM_NAME_URL_ENCODED => $refererEncoded])
            ->willReturn($url);

        $this->assertEquals($url, $this->model->getRemoveParams($this->wishlistItem, true));
    }

    public function testGetSharedAddToCartUrl()
    {
        $url = 'result url';
        $storeId = 1;
        $wishlistItemId = 1;
        $wishlistItemQty = 1;

        $this->wishlistItem->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->product);
        $this->wishlistItem->expects($this->once())
            ->method('getWishlistItemId')
            ->willReturn($wishlistItemId);
        $this->wishlistItem->expects($this->once())
            ->method('getQty')
            ->willReturn($wishlistItemQty);

        $this->product->expects($this->once())
            ->method('isVisibleInSiteVisibility')
            ->willReturn(true);
        $this->product->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->store->expects($this->once())
            ->method('getUrl')
            ->with('wishlist/shared/cart')
            ->willReturn($url);

        $expected = [
            'item' => $wishlistItemId,
            'qty' => $wishlistItemQty,
        ];
        $this->postDataHelper->expects($this->once())
            ->method('getPostData')
            ->with($url, $expected)
            ->willReturn($url);

        $this->assertEquals($url, $this->model->getSharedAddToCartUrl($this->wishlistItem));
    }

    public function testGetSharedAddAllToCartUrl()
    {
        $url = 'result url';

        $this->store->expects($this->once())
            ->method('getUrl')
            ->with('*/*/allcart', ['_current' => true])
            ->willReturn($url);

        $this->postDataHelper->expects($this->once())
            ->method('getPostData')
            ->with($url)
            ->willReturn($url);

        $this->assertEquals($url, $this->model->getSharedAddAllToCartUrl());
    }

    public function testGetRssUrlWithCustomerNotLogin()
    {
        $url = 'result url';

        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('wishlist/index/rss', [])
            ->willReturn($url);

        $this->assertEquals($url, $this->model->getRssUrl());
    }
}
