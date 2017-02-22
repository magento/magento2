<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Block\Rss;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class DiscountsTest
 * @package Magento\SalesRule\Block\Rss
 */
class DiscountsTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerInterface;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeModel;

    /**
     * @var \Magento\SalesRule\Model\Rss\Discounts|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $discounts;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rssBuilderInterface;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderInterface;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var \Magento\SalesRule\Model\Rss\Discounts|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rssModel;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $timezoneInterface;

    protected function setUp()
    {
        $this->storeManagerInterface = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->requestInterface = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->rssBuilderInterface = $this->getMock('Magento\Framework\App\Rss\UrlBuilderInterface');
        $this->urlBuilderInterface = $this->getMock('Magento\Framework\UrlInterface');
        $this->scopeConfigInterface = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->timezoneInterface = $this->getMock('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $this->discounts = $this->getMock('Magento\SalesRule\Model\Rss\Discounts', [], [], '', false);
        $this->rssModel = $this->getMock(
            'Magento\SalesRule\Model\Rss\Discounts',
            [
                '__wakeup',
                'getDiscountCollection'
            ],
            [],
            '',
            false
        );
        $this->storeModel = $this->getMock(
            'Magento\Store\Model\Store',
            [
                '__wakeUp',
                'getId',
                'getWebsiteId',
                'getName',
                'getFrontendName'
            ],
            [],
            '',
            false
        );

        $this->storeManagerInterface->expects($this->any())->method('getStore')
            ->will($this->returnValue($this->storeModel));
        $this->storeModel->expects($this->any())->method('getId')->will($this->returnValue(1));

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $this->objectManagerHelper->getObject(
            'Magento\SalesRule\Block\Rss\Discounts',
            [
                'storeManager' => $this->storeManagerInterface,
                'rssModel' => $this->discounts,
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

        $ruleModel =  $this->getMock(
            'Magento\SalesRule\Model\Rule',
            [
                '__wakeup',
                'getCouponCode',
                'getToDate',
                'getFromDate',
                'getDescription',
                'getName'
            ],
            [],
            '',
            false
        );

        $this->storeModel->expects($this->once())->method('getWebsiteId')->will($this->returnValue(1));
        $this->storeModel->expects($this->never())->method('getName');
        $this->storeModel->expects($this->atLeastOnce())->method('getFrontendName')->willReturn('Frontend Name');

        $this->requestInterface->expects($this->any())->method('getParam')->will($this->returnValue(1));
        $this->urlBuilderInterface->expects($this->any())->method('getUrl')->will($this->returnValue($url));
        $this->rssBuilderInterface->expects($this->any())->method('getUrl')->will($this->returnValue($rssUrl));
        $this->scopeConfigInterface->expects($this->any())->method('getValue')->will($this->returnValue('en_US'));
        $ruleModel->expects($this->any())->method('getCouponCode')->will($this->returnValue($ruleData['coupon_code']));
        $ruleModel->expects($this->any())->method('getToDate')->will($this->returnValue($ruleData['to_date']));
        $ruleModel->expects($this->once())->method('getFromDate')->will($this->returnValue($ruleData['from_date']));
        $ruleModel->expects($this->once())->method('getDescription')
            ->will($this->returnValue($ruleData['description']));
        $ruleModel->expects($this->once())->method('getName')->will($this->returnValue($ruleData['name']));
        $this->rssModel->expects($this->any())->method('getDiscountCollection')
            ->will($this->returnValue([$ruleModel]));
        $this->timezoneInterface->expects($this->any())->method('formatDateTime')->will($this->returnValue('12/12/14'));

        $data = $this->block->getRssData();

        $this->assertEquals($rssData['title'], $data['title']);
        $this->assertEquals($rssData['description'], $data['description']);
        $this->assertEquals($rssData['link'], $data['link']);
        $this->assertEquals($rssData['charset'], $data['charset']);
        $this->assertEquals($rssData['language'], $data['language']);
        $this->assertEquals($rssData['entries']['title'], $data['entries'][0]['title']);
        $this->assertEquals($rssData['entries']['link'], $data['entries'][0]['link']);
        $this->assertContains($rssData['entries']['description']['description'], $data['entries'][0]['description']);
        $this->assertContains($rssData['entries']['description']['start_date'], $data['entries'][0]['description']);
        $this->assertContains($rssData['entries']['description']['end_date'], $data['entries'][0]['description']);
        $this->assertContains($rssData['entries']['description']['coupon_code'], $data['entries'][0]['description']);
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
        $this->scopeConfigInterface->expects($this->once())->method('isSetFlag')->will($this->returnValue($isAllowed));
        $this->assertEquals($isAllowed, $this->block->isAllowed());
    }

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
            ->will($this->returnValue($feedData['link']));

        $this->scopeConfigInterface->expects($this->once())->method('isSetFlag')->will($this->returnValue(true));
        $this->assertEquals($feedData, $this->block->getFeeds());
    }
}
