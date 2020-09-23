<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Rss\Product;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Output;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Rss\Product\Special;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Msrp\Helper\Data as MsrpHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SpecialTest extends TestCase
{
    /**
     * @var \Magento\Catalog\Block\Rss\Product\Special
     */
    protected $block;

    /**
     * @var Context|MockObject
     */
    protected $httpContext;

    /**
     * @var Image|MockObject
     */
    protected $imageHelper;

    /**
     * @var Output|MockObject
     */
    protected $outputHelper;

    /**
     * @var \Magento\Catalog\Helper\Data|MockObject
     */
    protected $msrpHelper;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Catalog\Model\Rss\Product\Special|MockObject
     */
    protected $rssModel;

    /**
     * @var UrlBuilderInterface|MockObject
     */
    protected $rssUrlBuilder;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfig;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $localeDate;

    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    protected function setUp(): void
    {
        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->request->expects($this->at(0))->method('getParam')->with('store_id')->willReturn(null);
        $this->request->expects($this->at(1))->method('getParam')->with('cid')->willReturn(null);

        $this->httpContext = $this->getMockBuilder(Context::class)
            ->setMethods(['getValue'])->disableOriginalConstructor()
            ->getMock();
        $this->httpContext->expects($this->any())->method('getValue')->willReturn(1);

        $this->imageHelper = $this->createMock(Image::class);
        $this->outputHelper = $this->createPartialMock(Output::class, ['productAttribute']);
        $this->msrpHelper = $this->createPartialMock(MsrpHelper::class, ['canApplyMsrp']);
        $this->priceCurrency = $this->getMockForAbstractClass(PriceCurrencyInterface::class);
        $this->rssModel = $this->createMock(Special::class);
        $this->rssUrlBuilder = $this->getMockForAbstractClass(UrlBuilderInterface::class);

        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $store = $this->getMockBuilder(Store::class)
            ->setMethods(['getId', 'getFrontendName'])->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->any())->method('getId')->willReturn(1);
        $store->expects($this->any())->method('getFrontendName')->willReturn('Store 1');
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->scopeConfig->expects($this->any())->method('getValue')->willReturn('en_US');

        $this->localeDate = $this->getMockForAbstractClass(TimezoneInterface::class);

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

        $this->imageHelper->expects($this->once())->method('init')->with($item, 'rss_thumbnail')->willReturnSelf();
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
     * @return MockObject
     */
    protected function getItemMock()
    {
        $item = $this->getMockBuilder(Product::class)
            ->setMethods([
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
            ])->disableOriginalConstructor()
            ->getMock();
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
            ->with('rss/catalog/special', ScopeInterface::SCOPE_STORE)
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
            ->with('rss/catalog/special', ScopeInterface::SCOPE_STORE)
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
