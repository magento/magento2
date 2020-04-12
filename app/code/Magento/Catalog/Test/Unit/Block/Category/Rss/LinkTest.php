<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
        $this->urlBuilderInterface = $this->createMock(UrlBuilderInterface::class);
        $this->scopeConfigInterface = $this->createMock(ScopeConfigInterface::class);
        $this->storeManagerInterface = $this->createMock(StoreManagerInterface::class);
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
        $this->scopeConfigInterface->expects($this->once())->method('getValue')->will($this->returnValue($isAllowed));
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
        $categoryModel = $this->createPartialMock(Category::class, ['__wakeup', 'getLevel']);
        $this->registry->expects($this->once())->method('registry')->will($this->returnValue($categoryModel));
        $categoryModel->expects($this->any())->method('getLevel')->will($this->returnValue($categoryLevel));
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
        $this->urlBuilderInterface->expects($this->once())->method('getUrl')->will($this->returnValue($rssUrl));

        $categoryModel = $this->createPartialMock(Category::class, ['__wakeup', 'getId']);
        $this->registry->expects($this->once())->method('registry')->will($this->returnValue($categoryModel));
        $categoryModel->expects($this->any())->method('getId')->will($this->returnValue('1'));

        $storeModel = $this->createPartialMock(Category::class, ['__wakeup', 'getId']);
        $this->storeManagerInterface->expects($this->any())->method('getStore')->will($this->returnValue($storeModel));
        $storeModel->expects($this->any())->method('getId')->will($this->returnValue('1'));

        $this->assertEquals($rssUrl, $this->link->getLink());
    }
}
