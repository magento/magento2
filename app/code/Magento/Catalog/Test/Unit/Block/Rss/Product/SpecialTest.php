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
        $this->request = $this->createMock(RequestInterface::class);
        $this->request->expects($this->at(0))->method('getParam')->with('store_id')->will($this->returnValue(null));
        $this->request->expects($this->at(1))->method('getParam')->with('cid')->will($this->returnValue(null));

        $this->httpContext = $this->getMockBuilder(Context::class)
            ->setMethods(['getValue'])->disableOriginalConstructor()->getMock();
        $this->httpContext->expects($this->any())->method('getValue')->will($this->returnValue(1));

        $this->imageHelper = $this->createMock(Image::class);
        $this->outputHelper = $this->createPartialMock(Output::class, ['productAttribute']);
        $this->msrpHelper = $this->createPartialMock(MsrpHelper::class, ['canApplyMsrp']);
        $this->priceCurrency = $this->createMock(PriceCurrencyInterface::class);
        $this->rssModel = $this->createMock(Special::class);
        $this->rssUrlBuilder = $this->createMock(UrlBuilderInterface::class);

        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $store = $this->getMockBuilder(Store::class)
            ->setMethods(['getId', 'getFrontendName', '__wakeup'])->disableOriginalConstructor()->getMock();
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));
        $store->expects($this->any())->method('getFrontendName')->will($this->returnValue('Store 1'));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue('en_US'));

        $this->localeDate = $this->createMock(TimezoneInterface::class);

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

    /**
     * @return MockObject
     */
    protected function getItemMock()
    {
        $item = $this->getMockBuilder(Product::class)
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
            ->with('rss/catalog/special', ScopeInterface::SCOPE_STORE)
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
            ->with('rss/catalog/special', ScopeInterface::SCOPE_STORE)
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
