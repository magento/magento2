<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Rss\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class SpecialTest
 * Test for Special
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SpecialTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Block\Rss\Product\Special
     */
    protected $block;

    /**
     * @var \Magento\Framework\App\Http\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $httpContext;

    /**
     * @var \Magento\Catalog\Helper\Image|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $imageHelper;

    /**
     * @var \Magento\Catalog\Helper\Output|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $outputHelper;

    /**
     * @var \Magento\Catalog\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $msrpHelper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Catalog\Model\Rss\Product\Special|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $rssModel;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $rssUrlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    protected function setUp(): void
    {
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->request->expects($this->at(0))->method('getParam')->with('store_id')->willReturn(null);
        $this->request->expects($this->at(1))->method('getParam')->with('cid')->willReturn(null);

        $this->httpContext = $this->getMockBuilder(\Magento\Framework\App\Http\Context::class)
            ->setMethods(['getValue'])->disableOriginalConstructor()->getMock();
        $this->httpContext->expects($this->any())->method('getValue')->willReturn(1);

        $this->imageHelper = $this->createMock(\Magento\Catalog\Helper\Image::class);
        $this->outputHelper = $this->createPartialMock(\Magento\Catalog\Helper\Output::class, ['productAttribute']);
        $this->msrpHelper = $this->createPartialMock(\Magento\Msrp\Helper\Data::class, ['canApplyMsrp']);
        $this->priceCurrency = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $this->rssModel = $this->createMock(\Magento\Catalog\Model\Rss\Product\Special::class);
        $this->rssUrlBuilder = $this->createMock(\Magento\Framework\App\Rss\UrlBuilderInterface::class);

        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->setMethods(['getId', 'getFrontendName', '__wakeup'])->disableOriginalConstructor()->getMock();
        $store->expects($this->any())->method('getId')->willReturn(1);
        $store->expects($this->any())->method('getFrontendName')->willReturn('Store 1');
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $this->scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->scopeConfig->expects($this->any())->method('getValue')->willReturn('en_US');

        $this->localeDate = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);

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
            ->willReturn('http://magento.com/rss/feed/index/type/special_products/store_id/1');
        $item = $this->getItemMock();
        $this->rssModel->expects($this->once())->method('getProductsCollection')
            ->willReturn([$item]);
        $this->msrpHelper->expects($this->once())->method('canApplyMsrp')->willReturn(false);
        $this->localeDate->expects($this->once())->method('formatDateTime')->willReturn(date('Y-m-d'));

        $this->priceCurrency->expects($this->any())->method('convertAndFormat')->willReturnArgument(0);

        $this->imageHelper->expects($this->once())->method('init')->with($item, 'rss_thumbnail')
            ->willReturnSelf();
        $this->imageHelper->expects($this->once())->method('getUrl')
            ->willReturn('image_link');
        $this->outputHelper->expects($this->once())->method('productAttribute')->willReturn('');
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
        $this->assertStringContainsString('<a href="http://magento.com/product-name.html"><', $description);
        $this->assertStringContainsString(
            sprintf('<p>Price:  Special Price: 10<br />Special Expires On: %s</p>', date('Y-m-d')),
            $description
        );
        $this->assertStringContainsString(
            '<img src="image_link" alt="" border="0" align="left" height="75" width="75" />',
            $description
        );
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
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
        $item->expects($this->once())->method('getAllowedInRss')->willReturn(true);
        $item->expects($this->any())->method('getSpecialToDate')->willReturn(date('Y-m-d'));
        $item->expects($this->exactly(2))->method('getFinalPrice')->willReturn(10);
        $item->expects($this->once())->method('getSpecialPrice')->willReturn(15);
        $item->expects($this->exactly(2))->method('getAllowedPriceInRss')->willReturn(true);
        $item->expects($this->once())->method('getUseSpecial')->willReturn(true);
        $item->expects($this->once())->method('getDescription')->willReturn('Product Description');
        $item->expects($this->once())->method('getName')->willReturn('Product Name');
        $item->expects($this->exactly(2))->method('getProductUrl')
            ->willReturn('http://magento.com/product-name.html');

        return $item;
    }

    public function testIsAllowed()
    {
        $this->scopeConfig->expects($this->once())->method('isSetFlag')
            ->with('rss/catalog/special', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $this->assertTrue($this->block->isAllowed());
    }

    public function testGetCacheLifetime()
    {
        $this->assertEquals(600, $this->block->getCacheLifetime());
    }

    public function testGetFeeds()
    {
        $this->scopeConfig->expects($this->once())->method('isSetFlag')
            ->with('rss/catalog/special', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $this->rssUrlBuilder->expects($this->once())->method('getUrl')
            ->with(['type' => 'special_products'])
            ->willReturn('http://magento.com/rss/feed/index/type/special_products/store_id/1');
        $expected = [
            'label' => 'Special Products',
            'link' => 'http://magento.com/rss/feed/index/type/special_products/store_id/1',
        ];
        $this->assertEquals($expected, $this->block->getFeeds());
    }
}
