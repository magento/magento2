<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Block\Rss;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class DiscountsTest
 * Test for Discounts
 */
class DiscountsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Block\Rss\Discounts
     */
    protected $block;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerInterface;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeModel;

    /**
     * @var \Magento\SalesRule\Model\Rss\Discounts|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $discounts;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $rssBuilderInterface;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlBuilderInterface;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var \Magento\SalesRule\Model\Rss\Discounts|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $rssModel;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $timezoneInterface;

    protected function setUp(): void
    {
        $this->storeManagerInterface = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->requestInterface = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->rssBuilderInterface = $this->createMock(\Magento\Framework\App\Rss\UrlBuilderInterface::class);
        $this->urlBuilderInterface = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->scopeConfigInterface = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->timezoneInterface = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $this->discounts = $this->createMock(\Magento\SalesRule\Model\Rss\Discounts::class);
        $this->rssModel = $this->createPartialMock(\Magento\SalesRule\Model\Rss\Discounts::class, [
                '__wakeup',
                'getDiscountCollection'
            ]);
        $this->storeModel = $this->createPartialMock(\Magento\Store\Model\Store::class, [
                '__wakeUp',
                'getId',
                'getWebsiteId',
                'getName',
                'getFrontendName'
            ]);

        $this->storeManagerInterface->expects($this->any())->method('getStore')
            ->willReturn($this->storeModel);
        $this->storeModel->expects($this->any())->method('getId')->willReturn(1);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $this->objectManagerHelper->getObject(
            \Magento\SalesRule\Block\Rss\Discounts::class,
            [
                'storeManager' => $this->storeManagerInterface,
                'discounts' => $this->discounts,
                'rssUrlBuilder' => $this->rssBuilderInterface,
                'urlBuilder' => $this->urlBuilderInterface,
                'request' => $this->requestInterface,
                'scopeConfig' => $this->scopeConfigInterface,
                'rssModel' => $this->rssModel,
                'localeDate' => $this->timezoneInterface
            ]
        );
    }

    public function testGetRssData()
    {
        $ruleData = [
            'to_date' => '12/12/14',
            'from_date' => '12/12/14',
            'coupon_code' => '1234567',
            'description' => 'Rule Description',
            'name' => 'Rule Name',
        ];
        $rssData = [
            'title' => 'Frontend Name - Discounts and Coupons',
            'description' => 'Frontend Name - Discounts and Coupons',
            'link' => 'http://rss.magento.com/discount',
            'charset' => 'UTF-8',
            'language' => 'en_US',
            'entries' => [
                'title' => 'Rule Name',
                'link' => 'http://rss.magento.com',
                'description' => [
                        'description' => 'Rule Description',
                        'start_date' => '12/12/14',
                        'end_date' => '12/12/14',
                        'coupon_code' => '1234567',
                    ],
            ],
        ];
        $rssUrl = 'http://rss.magento.com/discount';
        $url = 'http://rss.magento.com';

        $ruleModel =  $this->createPartialMock(\Magento\SalesRule\Model\Rule::class, [
                '__wakeup',
                'getCouponCode',
                'getToDate',
                'getFromDate',
                'getDescription',
                'getName'
            ]);

        $this->storeModel->expects($this->once())->method('getWebsiteId')->willReturn(1);
        $this->storeModel->expects($this->never())->method('getName');
        $this->storeModel->expects($this->atLeastOnce())->method('getFrontendName')->willReturn('Frontend Name');

        $this->requestInterface->expects($this->any())->method('getParam')->willReturn(1);
        $this->urlBuilderInterface->expects($this->any())->method('getUrl')->willReturn($url);
        $this->rssBuilderInterface->expects($this->any())->method('getUrl')->willReturn($rssUrl);
        $this->scopeConfigInterface->expects($this->any())->method('getValue')->willReturn('en_US');
        $ruleModel->expects($this->any())->method('getCouponCode')->willReturn($ruleData['coupon_code']);
        $ruleModel->expects($this->any())->method('getToDate')->willReturn($ruleData['to_date']);
        $ruleModel->expects($this->once())->method('getFromDate')->willReturn($ruleData['from_date']);
        $ruleModel->expects($this->once())->method('getDescription')
            ->willReturn($ruleData['description']);
        $ruleModel->expects($this->once())->method('getName')->willReturn($ruleData['name']);
        $this->rssModel->expects($this->any())->method('getDiscountCollection')
            ->willReturn([$ruleModel]);
        $this->timezoneInterface->expects($this->any())->method('formatDateTime')->willReturn('12/12/14');

        $data = $this->block->getRssData();

        $this->assertEquals($rssData['title'], $data['title']);
        $this->assertEquals($rssData['description'], $data['description']);
        $this->assertEquals($rssData['link'], $data['link']);
        $this->assertEquals($rssData['charset'], $data['charset']);
        $this->assertEquals($rssData['language'], $data['language']);
        $this->assertEquals($rssData['entries']['title'], $data['entries'][0]['title']);
        $this->assertEquals($rssData['entries']['link'], $data['entries'][0]['link']);
        $this->assertStringContainsString(
            $rssData['entries']['description']['description'],
            $data['entries'][0]['description']
        );
        $this->assertStringContainsString(
            $rssData['entries']['description']['start_date'],
            $data['entries'][0]['description']
        );
        $this->assertStringContainsString(
            $rssData['entries']['description']['end_date'],
            $data['entries'][0]['description']
        );
        $this->assertStringContainsString(
            $rssData['entries']['description']['coupon_code'],
            $data['entries'][0]['description']
        );
    }

    public function testGetCacheLifetime()
    {
        $this->assertEquals(0, $this->block->getCacheLifetime());
    }

    /**
     * @dataProvider isAllowedDataProvider
     * @param bool $isAllowed
     */
    public function testIsAllowed($isAllowed)
    {
        $this->scopeConfigInterface->expects($this->once())->method('isSetFlag')->willReturn($isAllowed);
        $this->assertEquals($isAllowed, $this->block->isAllowed());
    }

    /**
     * @return array
     */
    public function isAllowedDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    public function testGetFeeds()
    {
        $feedData = [
            'label' => 'Coupons/Discounts',
            'link' => 'http://rss.magento.com/discount',
        ];
        $this->rssBuilderInterface->expects($this->any())
            ->method('getUrl')
            ->willReturn($feedData['link']);

        $this->scopeConfigInterface->expects($this->once())->method('isSetFlag')->willReturn(true);
        $this->assertEquals($feedData, $this->block->getFeeds());
    }
}
