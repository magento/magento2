<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Category\Rss;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class LinkTest
 * @package Magento\Catalog\Block\Category\Rss
 */
class LinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Block\Category\Rss\Link
     */
    protected $link;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlBuilderInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerInterface;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registry;

    protected function setUp(): void
    {
        $this->urlBuilderInterface = $this->createMock(\Magento\Framework\App\Rss\UrlBuilderInterface::class);
        $this->scopeConfigInterface = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->storeManagerInterface = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->registry = $this->createMock(\Magento\Framework\Registry::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->link = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Block\Category\Rss\Link::class,
            [
                'rssUrlBuilder' => $this->urlBuilderInterface,
                'registry' => $this->registry,
                'scopeConfig' => $this->scopeConfigInterface,
                'storeManager' => $this->storeManagerInterface
            ]
        );
    }

    /**
     * @dataProvider isRssAllowedDataProvider
     * @param bool $isAllowed
     */
    public function testIsRssAllowed($isAllowed)
    {
        $this->scopeConfigInterface->expects($this->once())->method('getValue')->willReturn($isAllowed);
        $this->assertEquals($isAllowed, $this->link->isRssAllowed());
    }

    /**
     * @return array
     */
    public function isRssAllowedDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    public function testGetLabel()
    {
        $this->assertEquals('Subscribe to RSS Feed', $this->link->getLabel());
    }

    /**
     * @dataProvider isTopCategoryDataProvider
     * @param bool $isTop
     * @param string $categoryLevel
     */
    public function testIsTopCategory($isTop, $categoryLevel)
    {
        $categoryModel = $this->createPartialMock(\Magento\Catalog\Model\Category::class, ['__wakeup', 'getLevel']);
        $this->registry->expects($this->once())->method('registry')->willReturn($categoryModel);
        $categoryModel->expects($this->any())->method('getLevel')->willReturn($categoryLevel);
        $this->assertEquals($isTop, $this->link->isTopCategory());
    }

    /**
     * @return array
     */
    public function isTopCategoryDataProvider()
    {
        return [
            [true, '2'],
            [false, '1']
        ];
    }

    public function testGetLink()
    {
        $rssUrl = 'http://rss.magento.com';
        $this->urlBuilderInterface->expects($this->once())->method('getUrl')->willReturn($rssUrl);

        $categoryModel = $this->createPartialMock(\Magento\Catalog\Model\Category::class, ['__wakeup', 'getId']);
        $this->registry->expects($this->once())->method('registry')->willReturn($categoryModel);
        $categoryModel->expects($this->any())->method('getId')->willReturn('1');

        $storeModel = $this->createPartialMock(\Magento\Catalog\Model\Category::class, ['__wakeup', 'getId']);
        $this->storeManagerInterface->expects($this->any())->method('getStore')->willReturn($storeModel);
        $storeModel->expects($this->any())->method('getId')->willReturn('1');

        $this->assertEquals($rssUrl, $this->link->getLink());
    }
}
