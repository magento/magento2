<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\Wishlist\Test\Unit\Model\Rss;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Output;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Directory\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Rss\Model\RssFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Wishlist\Block\Customer\Wishlist;
use Magento\Wishlist\Helper\Rss;
use Magento\Wishlist\Model\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WishlistTest extends TestCase
{
    /**
     * @var \Magento\Wishlist\Model\Rss\Wishlist
     */
    protected $model;

    /**
     * @var \Magento\Wishlist\Block\Customer\Wishlist
     */
    protected $wishlistBlock;

    /**
     * @var RssFactory
     */
    protected $rssFactoryMock;

    /**
     * @var UrlInterface
     */
    protected $urlBuilderMock;

    /**
     * @var Rss
     */
    protected $wishlistHelperMock;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Image
     */
    protected $imageHelperMock;

    /**
     * @var Output
     */
    protected $catalogOutputMock;

    /**
     * @var Output|MockObject
     */
    protected $layoutMock;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * Set up mock objects for tested class
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->catalogOutputMock = $this->createMock(Output::class);
        $this->rssFactoryMock = $this->createPartialMock(RssFactory::class, ['create']);
        $this->wishlistBlock = $this->createMock(Wishlist::class);
        $this->wishlistHelperMock = $this->createPartialMock(
            Rss::class,
            ['getWishlist', 'getCustomer', 'getCustomerName']
        );
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->imageHelperMock = $this->createMock(Image::class);

        $this->layoutMock = $this->getMockForAbstractClass(
            LayoutInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getBlock']
        );

        $this->customerFactory = $this->getMockBuilder(CustomerFactory::class)
            ->onlyMethods(['create'])->disableOriginalConstructor()
            ->getMock();

        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $requestMock->expects($this->any())->method('getParam')->with('sharing_code')
            ->willReturn('somesharingcode');

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Wishlist\Model\Rss\Wishlist::class,
            [
                'wishlistHelper' => $this->wishlistHelperMock,
                'wishlistBlock' => $this->wishlistBlock,
                'outputHelper' => $this->catalogOutputMock,
                'imageHelper' => $this->imageHelperMock,
                'urlBuilder' => $this->urlBuilderMock,
                'scopeConfig' => $this->scopeConfig,
                'rssFactory' => $this->rssFactoryMock,
                'layout' => $this->layoutMock,
                'request' => $requestMock,
                'customerFactory' => $this->customerFactory
            ]
        );
    }

    public function testGetRssData()
    {
        $wishlistId = 1;
        $customerName = 'Customer Name';
        $title = "$customerName's Wishlist";
        $wishlistModelMock = $this->getMockBuilder(\Magento\Wishlist\Model\Wishlist::class)
            ->addMethods(['getSharingCode'])
            ->onlyMethods(['getId', '__wakeup', 'getCustomerId', 'getItemCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $customerServiceMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $wishlistSharingUrl = 'wishlist/shared/index/1';
        $locale = 'en_US';
        $productUrl = 'http://product.url/';
        $productName = 'Product name';

        $customer = $this->getMockBuilder(Customer::class)
            ->onlyMethods(['getName', '__wakeup', 'load'])
            ->disableOriginalConstructor()
            ->getMock();
        $customer->expects($this->once())->method('load')->willReturnSelf();
        $customer->expects($this->once())->method('getName')->willReturn('Customer Name');

        $this->customerFactory->expects($this->once())->method('create')->willReturn($customer);

        $this->wishlistHelperMock->expects($this->any())
            ->method('getWishlist')
            ->willReturn($wishlistModelMock);
        $this->wishlistHelperMock->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customerServiceMock);
        $wishlistModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($wishlistId);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($wishlistSharingUrl);
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturnMap(

                    [
                        [
                            'advanced/modules_disable_output/Magento_Rss',
                            ScopeInterface::SCOPE_STORE,
                            null,
                            null,
                        ],
                        [
                            Data::XML_PATH_DEFAULT_LOCALE,
                            ScopeInterface::SCOPE_STORE,
                            null,
                            $locale
                        ],
                    ]

            );

        $staticArgs = [
            'productName' => $productName,
            'productUrl' => $productUrl,
        ];
        $description = $this->processWishlistItemDescription($wishlistModelMock, $staticArgs);

        $expectedResult = [
            'title' => $title,
            'description' => $title,
            'link' => $wishlistSharingUrl,
            'charset' => 'UTF-8',
            'entries' => [
                0 => [
                    'title' => $productName,
                    'link' => $productUrl,
                    'description' => $description,
                ],
            ],
        ];

        $this->assertEquals($expectedResult, $this->model->getRssData());
    }

    /**
     * Additional function to process forming description for wishlist item
     *
     * @param \Magento\Wishlist\Model\Wishlist $wishlistModelMock
     * @param array $staticArgs
     * @return string
     */
    protected function processWishlistItemDescription($wishlistModelMock, $staticArgs)
    {
        $imgThumbSrc = 'http://source-for-thumbnail';
        $priceHtmlForTest = '<div class="price">Price is 10 for example</div>';
        $productDescription = 'Product description';
        $productShortDescription = 'Product short description';

        $wishlistItem = $this->createMock(Item::class);
        $wishlistItemsCollection = [
            $wishlistItem,
        ];
        $productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['getAllowedInRss', 'getAllowedPriceInRss', 'getDescription', 'getShortDescription'])
            ->onlyMethods(['getName', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $wishlistModelMock->expects($this->once())
            ->method('getItemCollection')
            ->willReturn($wishlistItemsCollection);
        $wishlistItem->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);
        $productMock->expects($this->once())
            ->method('getAllowedPriceInRss')
            ->willReturn(true);
        $productMock->expects($this->once())
            ->method('getName')
            ->willReturn($staticArgs['productName']);
        $productMock->expects($this->once())
            ->method('getAllowedInRss')
            ->willReturn(true);
        $this->imageHelperMock->expects($this->once())
            ->method('init')
            ->with($productMock, 'rss_thumbnail')
            ->willReturnSelf();
        $this->imageHelperMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($imgThumbSrc);
        $priceRendererMock = $this->createPartialMock(Render::class, ['render']);

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->willReturn($priceRendererMock);
        $priceRendererMock->expects($this->once())
            ->method('render')
            ->willReturn($priceHtmlForTest);
        $productMock->expects($this->any())
            ->method('getDescription')
            ->willReturn($productDescription);
        $productMock->expects($this->any())
            ->method('getShortDescription')
            ->willReturn($productShortDescription);
        $this->catalogOutputMock->expects($this->any())
            ->method('productAttribute')
            ->willReturnArgument(1);
        $this->wishlistBlock
            ->expects($this->any())
            ->method('getProductUrl')
            ->with($productMock, ['_rss' => true])
            ->willReturn($staticArgs['productUrl']);

        $description = '<table><tr><td><a href="' . $staticArgs['productUrl'] . '"><img src="' . $imgThumbSrc .
            '" border="0" align="left" height="75" width="75"></a></td><td style="text-decoration:none;">' .
            $productShortDescription . '<p>' . $priceHtmlForTest . '</p><p>Comment: ' . $productDescription . '<p>' .
            '</td></tr></table>';

        return $description;
    }

    public function testIsAllowed()
    {
        $customerId = 1;
        $customerServiceMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $wishlist = $this->getMockBuilder(\Magento\Wishlist\Model\Wishlist::class)
            ->addMethods(['getSharingCode'])
            ->onlyMethods(['getId', '__wakeup', 'getCustomerId', 'getItemCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $wishlist->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->wishlistHelperMock->expects($this->any())->method('getWishlist')
            ->willReturn($wishlist);
        $this->wishlistHelperMock->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customerServiceMock);
        $customerServiceMock->expects($this->once())->method('getId')->willReturn($customerId);
        $this->scopeConfig->expects($this->once())->method('isSetFlag')
            ->with('rss/wishlist/active', ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $this->assertTrue($this->model->isAllowed());
    }

    public function testGetCacheKey()
    {
        $wishlistId = 1;
        $wishlist = $this->getMockBuilder(\Magento\Wishlist\Model\Wishlist::class)
            ->addMethods(['getSharingCode'])
            ->onlyMethods(['getId', '__wakeup', 'getCustomerId', 'getItemCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $wishlist->expects($this->once())->method('getId')->willReturn($wishlistId);
        $this->wishlistHelperMock->expects($this->any())->method('getWishlist')
            ->willReturn($wishlist);
        $this->assertEquals('rss_wishlist_data_1', $this->model->getCacheKey());
    }

    public function testGetCacheLifetime()
    {
        $this->assertEquals(60, $this->model->getCacheLifetime());
    }

    public function testIsAuthRequired()
    {
        $wishlist = $this->getMockBuilder(\Magento\Wishlist\Model\Wishlist::class)
            ->addMethods(['getSharingCode'])
            ->onlyMethods(['getId', '__wakeup', 'getCustomerId', 'getItemCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $wishlist->expects($this->any())->method('getSharingCode')
            ->willReturn('somesharingcode');
        $this->wishlistHelperMock->expects($this->any())->method('getWishlist')
            ->willReturn($wishlist);
        $this->assertFalse($this->model->isAuthRequired());
    }

    public function testGetProductPriceHtmlBlockDoesntExists()
    {
        $price = 10.;

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $renderBlockMock = $this->getMockBuilder(Render::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderBlockMock->expects($this->once())
            ->method('render')
            ->with(
                'wishlist_configured_price',
                $productMock,
                ['zone' => Render::ZONE_ITEM_LIST]
            )
            ->willReturn($price);

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->willReturn(false);
        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(
                Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            )
            ->willReturn($renderBlockMock);

        $this->assertEquals($price, $this->model->getProductPriceHtml($productMock));
    }

    public function testGetProductPriceHtmlBlockExists()
    {
        $price = 10.;

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $renderBlockMock = $this->getMockBuilder(Render::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderBlockMock->expects($this->once())
            ->method('render')
            ->with(
                'wishlist_configured_price',
                $productMock,
                ['zone' => Render::ZONE_ITEM_LIST]
            )
            ->willReturn($price);

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->willReturn($renderBlockMock);

        $this->assertEquals($price, $this->model->getProductPriceHtml($productMock));
    }
}
