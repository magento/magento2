<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Rss\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class SpecialTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SpecialTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Rss\Product\Special
     */
    protected $block;

    /**
     * @var \Magento\Framework\App\Http\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpContext;

    /**
     * @var \Magento\Catalog\Helper\Image|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageHelper;

    /**
     * @var \Magento\Catalog\Helper\Output|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $outputHelper;

    /**
     * @var \Magento\Catalog\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $msrpHelper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Catalog\Model\Rss\Product\Special|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rssModel;

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
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    protected function setUp()
    {
        $this->request = $this->getMock(\Magento\Framework\App\RequestInterface::class);
        $this->request->expects($this->at(0))->method('getParam')->with('store_id')->will($this->returnValue(null));
        $this->request->expects($this->at(1))->method('getParam')->with('cid')->will($this->returnValue(null));

        $this->httpContext = $this->getMockBuilder(\Magento\Framework\App\Http\Context::class)
            ->setMethods(['getValue'])->disableOriginalConstructor()->getMock();
        $this->httpContext->expects($this->any())->method('getValue')->will($this->returnValue(1));

        $this->imageHelper = $this->getMock(\Magento\Catalog\Helper\Image::class, [], [], '', false);
        $this->outputHelper = $this->getMock(
            \Magento\Catalog\Helper\Output::class,
            ['productAttribute'],
            [],
            '',
            false
        );
        $this->msrpHelper = $this->getMock(\Magento\Msrp\Helper\Data::class, ['canApplyMsrp'], [], '', false);
        $this->priceCurrency = $this->getMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $this->rssModel = $this->getMock(\Magento\Catalog\Model\Rss\Product\Special::class, [], [], '', false);
        $this->rssUrlBuilder = $this->getMock(\Magento\Framework\App\Rss\UrlBuilderInterface::class);

        $this->storeManager = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->setMethods(['getId', 'getFrontendName', '__wakeup'])->disableOriginalConstructor()->getMock();
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));
        $store->expects($this->any())->method('getFrontendName')->will($this->returnValue('Store 1'));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->scopeConfig = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue('en_US'));

        $this->localeDate = $this->getMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $objectManagerHelper->getObject(
            \Magento\Catalog\Block\Rss\Product\Special::class,
            [
                'request' => $this->request,
                'httpContext' => $this->httpContext,
                'imageHelper' => $this->imageHelper,
                'outputHelper' => $this->outputHelper,
                'msrpHelper' => $this->msrpHelper,
                'priceCurrency' => $this->priceCurrency,
                'rssModel' => $this->rssModel,
                'rssUrlBuilder' => $this->rssUrlBuilder,
                'storeManager' => $this->storeManager,
                'scopeConfig' => $this->scopeConfig,
                'localeDate' => $this->localeDate,
            ]
        );
    }

    public function testGetRssData()
    {
        $this->rssUrlBuilder->expects($this->once())->method('getUrl')
            ->with(['type' => 'special_products', 'store_id' => 1])
            ->will($this->returnValue('http://magento.com/rss/feed/index/type/special_products/store_id/1'));
        $item = $this->getItemMock();
        $this->rssModel->expects($this->once())->method('getProductsCollection')
            ->will($this->returnValue([$item]));
        $this->msrpHelper->expects($this->once())->method('canApplyMsrp')->will($this->returnValue(false));
        $this->localeDate->expects($this->once())->method('formatDateTime')->will($this->returnValue(date('Y-m-d')));

        $this->priceCurrency->expects($this->any())->method('convertAndFormat')->will($this->returnArgument(0));

        $this->imageHelper->expects($this->once())->method('init')->with($item, 'rss_thumbnail')
            ->will($this->returnSelf());
        $this->imageHelper->expects($this->once())->method('getUrl')
            ->will($this->returnValue('image_link'));
        $this->outputHelper->expects($this->once())->method('productAttribute')->will($this->returnValue(''));
        $data = [
            'title' => 'Store 1 - Special Products',
            'description' => 'Store 1 - Special Products',
            'link' => 'http://magento.com/rss/feed/index/type/special_products/store_id/1',
            'charset' => 'UTF-8',
            'language' => 'en_US',
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
        $this->assertContains('<a href="http://magento.com/product-name.html"><', $description);
        $this->assertContains(
            sprintf('<p>Price:  Special Price: 10<br />Special Expires On: %s</p>', date('Y-m-d')),
            $description
        );
        $this->assertContains(
            '<img src="image_link" alt="" border="0" align="left" height="75" width="75" />',
            $description
        );
    }

    protected function getItemMock()
    {
        $item = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods([
                '__wakeup',
                '__sleep',
                'getName',
                'getProductUrl',
                'getDescription',
                'getAllowedInRss',
                'getAllowedPriceInRss',
                'getSpecialToDate',
                'getSpecialPrice',
                'getFinalPrice',
                'getPrice',
                'getUseSpecial',
            ])->disableOriginalConstructor()->getMock();
        $item->expects($this->once())->method('getAllowedInRss')->will($this->returnValue(true));
        $item->expects($this->any())->method('getSpecialToDate')->will($this->returnValue(date('Y-m-d')));
        $item->expects($this->exactly(2))->method('getFinalPrice')->will($this->returnValue(10));
        $item->expects($this->once())->method('getSpecialPrice')->will($this->returnValue(15));
        $item->expects($this->exactly(2))->method('getAllowedPriceInRss')->will($this->returnValue(true));
        $item->expects($this->once())->method('getUseSpecial')->will($this->returnValue(true));
        $item->expects($this->once())->method('getDescription')->will($this->returnValue('Product Description'));
        $item->expects($this->once())->method('getName')->will($this->returnValue('Product Name'));
        $item->expects($this->exactly(2))->method('getProductUrl')
            ->will($this->returnValue('http://magento.com/product-name.html'));

        return $item;
    }

    public function testIsAllowed()
    {
        $this->scopeConfig->expects($this->once())->method('isSetFlag')
            ->with('rss/catalog/special', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue(true));
        $this->assertEquals(true, $this->block->isAllowed());
    }

    public function testGetCacheLifetime()
    {
        $this->assertEquals(600, $this->block->getCacheLifetime());
    }

    public function testGetFeeds()
    {
        $this->scopeConfig->expects($this->once())->method('isSetFlag')
            ->with('rss/catalog/special', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue(true));
        $this->rssUrlBuilder->expects($this->once())->method('getUrl')
            ->with(['type' => 'special_products'])
            ->will($this->returnValue('http://magento.com/rss/feed/index/type/special_products/store_id/1'));
        $expected = [
            'label' => 'Special Products',
            'link' => 'http://magento.com/rss/feed/index/type/special_products/store_id/1',
        ];
        $this->assertEquals($expected, $this->block->getFeeds());
    }
}
