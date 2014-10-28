<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Wishlist\Model\Rss;

class WishlistTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Rss\Model\RssFactory
     */
    protected $rssFactoryMock;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilderMock;

    /**
     * @var \Magento\Wishlist\Helper\Rss
     */
    protected $wishlistHelperMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelperMock;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    protected $catalogOutputMock;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    protected $layoutMock;

    /**
     * Set up mock objects for tested class
     *
     * @return void
     */
    public function setUp()
    {
        $this->catalogOutputMock = $this->getMock('Magento\Catalog\Helper\Output', [], [], '', false);
        $this->rssFactoryMock = $this->getMock('Magento\Rss\Model\RssFactory', ['create'], [], '', false);
        $this->wishlistBlock = $this->getMock('\Magento\Wishlist\Block\Customer\Wishlist', [], [], '', false);
        $this->wishlistHelperMock = $this->getMock(
            'Magento\Wishlist\Helper\Rss',
            ['getWishlist', 'getCustomer', 'getCustomerName'],
            [],
            '',
            false
        );
        $this->urlBuilderMock = $this->getMockForAbstractClass(
            'Magento\Framework\UrlInterface',
            [],
            '',
            true,
            true,
            true,
            ['getUrl']
        );
        $this->scopeConfig = $this->getMockForAbstractClass(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            '',
            true,
            true,
            true,
            ['getConfig']
        );
        $this->imageHelperMock = $this->getMock('Magento\Catalog\Helper\Image', [], [], '', false);

        $this->layoutMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\LayoutInterface',
            [],
            '',
            true,
            true,
            true,
            ['getBlock']
        );

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Wishlist\Model\Rss\Wishlist',
            [
                'wishlistHelper' => $this->wishlistHelperMock,
                'wishlistBlock' => $this->wishlistBlock,
                'outputHelper' => $this->catalogOutputMock,
                'imageHelper'=> $this->imageHelperMock,
                'urlBuilder' => $this->urlBuilderMock,
                'scopeConfig' => $this->scopeConfig,
                'rssFactory' => $this->rssFactoryMock,
                'layout' => $this->layoutMock,
            ]
        );
    }

    public function testGetRssData()
    {
        $wishlistId = 1;
        $customerName = 'Customer Name';
        $title = "$customerName's Wishlist";
        $wishlistModelMock = $this->getMock(
            'Magento\Wishlist\Model\Wishlist',
            ['getId', '__wakeup', 'getCustomerId', 'getItemCollection'],
            [],
            '',
            false
        );
        $customerServiceMock = $this->getMock('Magento\Customer\Service\V1\Data\Customer', [], [], '', false);
        $wishlistSharingUrl = 'wishlist/shared/index/1';
        $locale = 'en_US';
        $productUrl = 'http://product.url/';
        $productName = 'Product name';

        $this->wishlistHelperMock->expects($this->any())
            ->method('getWishlist')
            ->will($this->returnValue($wishlistModelMock));
        $this->wishlistHelperMock->expects($this->any())
            ->method('getCustomer')
            ->will($this->returnValue($customerServiceMock));
        $this->wishlistHelperMock->expects($this->once())
            ->method('getCustomerName')
            ->will($this->returnValue($customerName));
        $wishlistModelMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($wishlistId));
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue($wishlistSharingUrl));
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap(
                    [
                        [
                            'advanced/modules_disable_output/Magento_Rss',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            null,
                            null
                        ],
                        [
                            \Magento\Core\Helper\Data::XML_PATH_DEFAULT_LOCALE,
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            null,
                            $locale
                        ]
                    ]
                )
            );

        $staticArgs = [
            'productName' => $productName,
            'productUrl' => $productUrl
        ];
        $description = $this->processWishlistItemDescription($wishlistModelMock, $staticArgs);

        $expectedResult = array(
            'title' => $title,
            'description' => $title,
            'link' => $wishlistSharingUrl,
            'charset' => 'UTF-8',
            'entries' => array(
                0 => array(
                    'title' => $productName,
                    'link' => $productUrl,
                    'description' => $description
                )
            )
        );


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

        $wishlistItem = $this->getMock('Magento\Wishlist\Model\Item', [], [], '', false);
        $wishlistItemsCollection = [
            $wishlistItem
        ];
        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            [
                'getAllowedInRss',
                'getAllowedPriceInRss',
                'getDescription',
                'getShortDescription',
                'getName',
                '__wakeup'
            ],
            [],
            '',
            false
        );

        $wishlistModelMock->expects($this->once())
            ->method('getItemCollection')
            ->will($this->returnValue($wishlistItemsCollection));
        $wishlistItem->expects($this->once())
            ->method('getProduct')
            ->will($this->returnValue($productMock));
        $productMock->expects($this->once())
            ->method('getAllowedPriceInRss')
            ->will($this->returnValue(true));
        $productMock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($staticArgs['productName']));
        $productMock->expects($this->once())
            ->method('getAllowedInRss')
            ->will($this->returnValue(true));
        $this->imageHelperMock->expects($this->once())
            ->method('init')
            ->will($this->returnSelf());
        $this->imageHelperMock->expects($this->once())
            ->method('resize')
            ->will($this->returnValue($imgThumbSrc));
        $priceRendererMock = $this->getMock('Magento\Framework\Pricing\Render', ['render'], [], '', false);

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue($priceRendererMock));
        $priceRendererMock->expects($this->once())
            ->method('render')
            ->will($this->returnValue($priceHtmlForTest));
        $productMock->expects($this->any())
            ->method('getDescription')
            ->will($this->returnValue($productDescription));
        $productMock->expects($this->any())
            ->method('getShortDescription')
            ->will($this->returnValue($productShortDescription));
        $this->catalogOutputMock->expects($this->any())
            ->method('productAttribute')
            ->will($this->returnArgument(1));
        $this->wishlistBlock
            ->expects($this->any())
            ->method('getProductUrl')
            ->with($productMock, ['_rss' => true])
            ->will($this->returnValue($staticArgs['productUrl']));

        $description = '<table><tr><td><a href="' . $staticArgs['productUrl'] . '"><img src="' . $imgThumbSrc .
            '" border="0" align="left" height="75" width="75"></a></td><td style="text-decoration:none;">' .
            $productShortDescription . '<p>' . $priceHtmlForTest . '</p><p>Comment: ' . $productDescription . '<p>' .
            '</td></tr></table>';

        return $description;
    }

    public function testIsAllowed()
    {
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with('rss/wishlist/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue(true));
        $this->assertTrue($this->model->isAllowed());
    }

    public function testGetCacheKey()
    {
        $this->assertEquals('rss_wishlist_data', $this->model->getCacheKey());
    }

    public function testGetCacheLifetime()
    {
        $this->assertEquals(60, $this->model->getCacheLifetime());
    }
}
