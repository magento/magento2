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
class LinkTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerInterface;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    protected function setUp()
    {
        $this->urlBuilderInterface = $this->getMock('Magento\Framework\App\Rss\UrlBuilderInterface');
        $this->scopeConfigInterface = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->storeManagerInterface = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->registry = $this->getMock('Magento\Framework\Registry');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->link = $this->objectManagerHelper->getObject(
            'Magento\Catalog\Block\Category\Rss\Link',
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
        $categoryModel = $this->getMock('\Magento\Catalog\Model\Category', ['__wakeup', 'getLevel'], [], '', false);
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

        $categoryModel = $this->getMock('\Magento\Catalog\Model\Category', ['__wakeup', 'getId'], [], '', false);
        $this->registry->expects($this->once())->method('registry')->will($this->returnValue($categoryModel));
        $categoryModel->expects($this->any())->method('getId')->will($this->returnValue('1'));

        $storeModel = $this->getMock('\Magento\Store\Model\Category', ['__wakeup', 'getId'], [], '', false);
        $this->storeManagerInterface->expects($this->any())->method('getStore')->will($this->returnValue($storeModel));
        $storeModel->expects($this->any())->method('getId')->will($this->returnValue('1'));

        $this->assertEquals($rssUrl, $this->link->getLink());
    }
}
