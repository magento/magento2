<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Rss\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class NewProductsTest
 * @package Magento\Catalog\Block\Rss\Product
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewProductsTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
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
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->request->expects($this->any())->method('getParam')->with('store_id')->will($this->returnValue(null));

        $this->context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->imageHelper = $this->createMock(\Magento\Catalog\Helper\Image::class);
        $this->newProducts = $this->createMock(\Magento\Catalog\Model\Rss\Product\NewProducts::class);
        $this->rssUrlBuilder = $this->createMock(\Magento\Framework\App\Rss\UrlBuilderInterface::class);
        $this->scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->setMethods(['getId', 'getFrontendName', '__wakeup'])->disableOriginalConstructor()->getMock();
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));
        $store->expects($this->any())->method('getFrontendName')->will($this->returnValue('Store 1'));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Block\Rss\Product\NewProducts::class,
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

    /**
     * @return array
     */
    public function isAllowedDataProvider()
    {
        return [
            [1, true],
            [0, false]
        ];
    }

    /**
     * @dataProvider isAllowedDataProvider
     */
    public function testIsAllowed($configValue, $expectedResult)
    {
        $this->scopeConfig->expects($this->once())->method('isSetFlag')->will($this->returnValue($configValue));
        $this->assertEquals($expectedResult, $this->block->isAllowed());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
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
            '__wakeup',
        ];
        $item = $this->createPartialMock(\Magento\Catalog\Model\Product::class, $methods);
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
            ->with(['type' => 'new_products', 'store_id' => 1])
            ->will($this->returnValue('http://magento.com/rss/feed/index/type/new_products/store_id/1'));
        $item = $this->getItemMock();
        $this->newProducts->expects($this->once())->method('getProductsCollection')
            ->will($this->returnValue([$item]));
        $this->imageHelper->expects($this->once())->method('init')->with($item, 'rss_thumbnail')
            ->will($this->returnSelf());
        $this->imageHelper->expects($this->once())->method('getUrl')
            ->will($this->returnValue('image_link'));
        $data = [
            'title' => 'New Products from Store 1',
            'description' => 'New Products from Store 1',
            'link' => 'http://magento.com/rss/feed/index/type/new_products/store_id/1',
            'charset' => 'UTF-8',
            'language' => null,
            'entries' => [
                [
                    'title' => 'Product Name',
                    'link' => 'http://magento.com/product-name.html',
                ],
            ],
        ];
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
        $rssUrl = 'http://magento.com/rss/feed/index/type/new_products/store_id/1';
        $this->rssUrlBuilder->expects($this->once())->method('getUrl')
            ->with(['type' => 'new_products'])
            ->will($this->returnValue($rssUrl));
        $expected = [
            'label' => 'New Products',
            'link' => $rssUrl,
        ];
        $this->assertEquals($expected, $this->block->getFeeds());
    }
}
