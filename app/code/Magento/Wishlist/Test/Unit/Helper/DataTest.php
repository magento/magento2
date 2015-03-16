<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $wishlistHelper;

    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $configureUrl;

    /**
     * Set up mock objects for tested class
     *
     * @return void
     */
    public function setUp()
    {
        $this->url = 'http://magento.com/wishlist/index/index/wishlist_id/1/?___store=default';
        $encoded = 'encodedUrl';

        $this->configureUrl = 'http://magento2ce/wishlist/index/configure/id/4/product_id/30/';

        $urlEncoder = $this->getMock('Magento\Framework\Url\EncoderInterface', [], [], '', false);
        $urlEncoder->expects($this->any())
            ->method('encode')
            ->with($this->url)
            ->will($this->returnValue($encoded));

        $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $store->expects($this->any())
            ->method('getUrl')
            ->with('wishlist/index/cart', ['item' => '%item%', 'uenc' => $encoded])
            ->will($this->returnValue($this->url));

        $storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        $urlBuilder = $this->getMock('Magento\Framework\UrlInterface\Proxy', ['getUrl'], [], '', false);
        if ($this->getName() == 'testGetConfigureUrl') {
            $urlBuilder->expects($this->once())
                ->method('getUrl')
                ->with('wishlist/index/configure', ['id' => 4, 'product_id' => 30])
                ->will($this->returnValue($this->configureUrl));
        } else {
            $urlBuilder->expects($this->any())
                ->method('getUrl')
                ->with('wishlist/index/index', ['_current' => true, '_use_rewrite' => true, '_scope_to_url' => true])
                ->will($this->returnValue($this->url));
        }

        $context = $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false);
        $context->expects($this->once())
            ->method('getUrlBuilder')
            ->will($this->returnValue($urlBuilder));
        $context->expects($this->once())
            ->method('getUrlEncoder')
            ->will($this->returnValue($urlEncoder));

        $this->wishlistProvider = $this->getMock(
            'Magento\Wishlist\Controller\WishlistProviderInterface',
            ['getWishlist'],
            [],
            '',
            false
        );

        $this->coreRegistry = $this->getMock(
            '\Magento\Framework\Registry',
            ['registry'],
            [],
            '',
            false
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->wishlistHelper = $objectManager->getObject(
            'Magento\Wishlist\Helper\Data',
            [
                'context' => $context,
                'storeManager' => $storeManager,
                'wishlistProvider' => $this->wishlistProvider,
                'coreRegistry' => $this->coreRegistry
            ]
        );
    }

    public function testGetAddToCartUrl()
    {
        $this->assertEquals($this->url, $this->wishlistHelper->getAddToCartUrl('%item%'));
    }

    public function testGetConfigureUrl()
    {
        $wishlistItem = $this->getMock(
            'Magento\Wishlist\Model\Item',
            ['getWishlistItemId', 'getProductId'],
            [],
            '',
            false
        );
        $wishlistItem
            ->expects($this->once())
            ->method('getWishlistItemId')
            ->will($this->returnValue(4));
        $wishlistItem
            ->expects($this->once())
            ->method('getProductId')
            ->will($this->returnValue(30));

        $this->assertEquals($this->configureUrl, $this->wishlistHelper->getConfigureUrl($wishlistItem));
    }

    public function testGetWishlist()
    {
        $wishlist = $this->getMock('\Magento\Wishlist\Model\Wishlist', [], [], '', false);
        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->will($this->returnValue($wishlist));

        $this->assertEquals($wishlist, $this->wishlistHelper->getWishlist());
    }

    public function testGetWishlistWithCoreRegistry()
    {
        $wishlist = $this->getMock('\Magento\Wishlist\Model\Wishlist', [], [], '', false);
        $this->coreRegistry->expects($this->any())
            ->method('registry')
            ->will($this->returnValue($wishlist));

        $this->assertEquals($wishlist, $this->wishlistHelper->getWishlist());
    }
}
