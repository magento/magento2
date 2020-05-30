<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Helper;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Item as WishlistItem;
use Magento\Wishlist\Model\Wishlist;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends TestCase
{
    /** @var  Data */
    protected $model;

    /** @var  WishlistProviderInterface|MockObject */
    protected $wishlistProvider;

    /** @var  Registry|MockObject */
    protected $coreRegistry;

    /** @var  PostHelper|MockObject */
    protected $postDataHelper;

    /** @var  WishlistItem|MockObject */
    protected $wishlistItem;

    /** @var  Product|MockObject */
    protected $product;

    /** @var  StoreManagerInterface|MockObject */
    protected $storeManager;

    /** @var  Store|MockObject */
    protected $store;

    /** @var  UrlInterface|MockObject */
    protected $urlBuilder;

    /** @var  Wishlist|MockObject */
    protected $wishlist;

    /** @var  EncoderInterface|MockObject */
    protected $urlEncoderMock;

    /** @var  RequestInterface|MockObject */
    protected $requestMock;

    /** @var  Context|MockObject */
    protected $context;

    /** @var  Session|MockObject */
    protected $customerSession;

    /**
     * Set up mock objects for tested class
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);

        $this->urlEncoderMock = $this->getMockBuilder(EncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getServer'])
            ->getMockForAbstractClass();

        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->context = $this->getMockBuilder(Context::class)
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

        $this->wishlistProvider = $this->getMockBuilder(WishlistProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->coreRegistry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->postDataHelper = $this->getMockBuilder(PostHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->wishlistItem = $this->getMockBuilder(WishlistItem::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getProduct',
                    'getWishlistItemId',
                    'getQty',
                ]
            )->getMock();

        $this->wishlist = $this->getMockBuilder(Wishlist::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Data::class,
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

        /** @var WishlistItem|MockObject $wishlistItem */
        $wishlistItem = $this->getMockBuilder(WishlistItem::class)
            ->addMethods(['getWishlistItemId', 'getProductId'])
            ->disableOriginalConstructor()
            ->getMock();
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
