<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Category\Rss;

use Magento\Catalog\Block\Category\Rss\Link;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    /**
     * @var Link
     */
    protected $link;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var UrlBuilderInterface|MockObject
     */
    protected $urlBuilderInterface;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerInterface;

    /**
     * @var Registry|MockObject
     */
    protected $registry;

    protected function setUp(): void
    {
        $this->urlBuilderInterface = $this->getMockForAbstractClass(UrlBuilderInterface::class);
        $this->scopeConfigInterface = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->storeManagerInterface = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->registry = $this->createMock(Registry::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->link = $this->objectManagerHelper->getObject(
            Link::class,
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
    public static function isRssAllowedDataProvider()
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
        $categoryModel = $this->createPartialMock(Category::class, [ 'getLevel']);
        $this->registry->expects($this->once())->method('registry')->willReturn($categoryModel);
        $categoryModel->expects($this->any())->method('getLevel')->willReturn($categoryLevel);
        $this->assertEquals($isTop, $this->link->isTopCategory());
    }

    /**
     * @return array
     */
    public static function isTopCategoryDataProvider()
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

        $categoryModel = $this->createPartialMock(Category::class, [ 'getId']);
        $this->registry->expects($this->once())->method('registry')->willReturn($categoryModel);
        $categoryModel->expects($this->any())->method('getId')->willReturn('1');

        $storeModel = $this->createPartialMock(Category::class, [ 'getId']);
        $this->storeManagerInterface->expects($this->any())->method('getStore')->willReturn($storeModel);
        $storeModel->expects($this->any())->method('getId')->willReturn('1');

        $this->assertEquals($rssUrl, $this->link->getLink());
    }
}
