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
namespace Magento\Catalog\Block\Rss\Product;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class NewProductsTest
 * @package Magento\Catalog\Block\Rss\Product
 */
class NewProductsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Rss\Product\NewProducts
     */
    protected $block;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Catalog\Helper\Image|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageHelper;

    /**
     * @var \Magento\Catalog\Model\Rss\Product\NewProducts|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $newProducts;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rssUrlBuilder;

    /**
     * @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    protected function setUp()
    {
        $this->request = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->request->expects($this->any())->method('getParam')->with('store_id')->will($this->returnValue(null));

        $this->context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $this->imageHelper = $this->getMock('Magento\Catalog\Helper\Image', [], [], '', false);
        $this->newProducts = $this->getMock('Magento\Catalog\Model\Rss\Product\NewProducts', [], [], '', false);
        $this->rssUrlBuilder = $this->getMock('Magento\Framework\App\Rss\UrlBuilderInterface');
        $this->scopeConfig = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');

        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);
        $store = $this->getMockBuilder('\Magento\Store\Model\Store')
            ->setMethods(['getId', 'getFrontendName', '__wakeup'])->disableOriginalConstructor()->getMock();
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));
        $store->expects($this->any())->method('getFrontendName')->will($this->returnValue('Store 1'));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $this->objectManagerHelper->getObject(
            'Magento\Catalog\Block\Rss\Product\NewProducts',
            [
                'request' => $this->request,
                'imageHelper' => $this->imageHelper,
                'rssModel' => $this->newProducts,
                'rssUrlBuilder' => $this->rssUrlBuilder,
                'storeManager' => $this->storeManager,
                'scopeConfig' => $this->scopeConfig,
            ]
        );
    }

    public function isAllowedDataProvider()
    {
        return array(
            array(1, true),
            array(0, false)
        );
    }
    /**
     * @dataProvider isAllowedDataProvider
     */
    public function testIsAllowed($configValue, $expectedResult)
    {
        $this->scopeConfig->expects($this->once())->method('isSetFlag')->will($this->returnValue($configValue));
        $this->assertEquals($expectedResult, $this->block->isAllowed());
    }

    protected function getItemMock()
    {
        $methods = [
            'setAllowedInRss',
            'setAllowedPriceInRss',
            'getAllowedPriceInRss',
            'getAllowedInRss',
            'getProductUrl',
            'getDescription',
            'getName',
            '__wakeup'
        ];
        $item = $this->getMock('\Magento\Catalog\Model\Product', $methods, [], '', false);
        $item->expects($this->once())->method('setAllowedInRss')->with(true);
        $item->expects($this->once())->method('setAllowedPriceInRss')->with(true);
        $item->expects($this->once())->method('getAllowedPriceInRss')->will($this->returnValue(true));
        $item->expects($this->once())->method('getAllowedInRss')->will($this->returnValue(true));
        $item->expects($this->once())->method('getDescription')->will($this->returnValue('Product Description'));
        $item->expects($this->once())->method('getName')->will($this->returnValue('Product Name'));
        $item->expects($this->any())->method('getProductUrl')->will(
            $this->returnValue('http://magento.com/product-name.html')
        );
        return $item;
    }

    public function testGetRssData()
    {
        $this->rssUrlBuilder->expects($this->once())->method('getUrl')
            ->with(array('type' => 'new_products', 'store_id' => 1))
            ->will($this->returnValue('http://magento.com/rss/feed/index/type/new_products/store_id/1'));
        $item = $this->getItemMock();
        $this->newProducts->expects($this->once())->method('getProductsCollection')
            ->will($this->returnValue([$item]));
        $this->imageHelper->expects($this->once())->method('init')->with($item, 'thumbnail')
            ->will($this->returnSelf());
        $this->imageHelper->expects($this->once())->method('resize')->with(75, 75)
            ->will($this->returnValue('image_link'));
        $data = array(
            'title' => 'New Products from Store 1',
            'description' => 'New Products from Store 1',
            'link' => 'http://magento.com/rss/feed/index/type/new_products/store_id/1',
            'charset' => 'UTF-8',
            'language' => null,
            'entries' => array(
                array(
                    'title' => 'Product Name',
                    'link' => 'http://magento.com/product-name.html',
                )
            )
        );
        $rssData = $this->block->getRssData();
        $description = $rssData['entries'][0]['description'];
        unset($rssData['entries'][0]['description']);
        $this->assertEquals($data, $rssData);
        $this->assertContains('<a href="http://magento.com/product-name.html">', $description);
        $this->assertContains('<img src="image_link" border="0" align="left" height="75" width="75">', $description);
        $this->assertContains('<td style="text-decoration:none;">Product Description </td>', $description);
    }

    public function testGetCacheLifetime()
    {
        $this->assertEquals(600, $this->block->getCacheLifetime());
    }

    public function testGetFeeds()
    {
        $this->scopeConfig->expects($this->once())->method('isSetFlag')->will($this->returnValue(true));
        $rssUrl ='http://magento.com/rss/feed/index/type/new_products/store_id/1';
        $this->rssUrlBuilder->expects($this->once())->method('getUrl')
            ->with(array('type' => 'new_products'))
            ->will($this->returnValue($rssUrl));
        $expected = array(
            'label' => 'New Products',
            'link' => $rssUrl
        );
        $this->assertEquals($expected, $this->block->getFeeds());
    }
}
