<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Rss\Order\Grid;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class LinkTest
 * @package Magento\Sales\Block\Adminhtml\Rss\Order\Grid
 */
class LinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Block\Adminhtml\Rss\Order\Grid\Link
     */
    protected $link;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigInterface;

    protected function setUp()
    {
        $this->context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->urlBuilderInterface = $this->createMock(\Magento\Framework\App\Rss\UrlBuilderInterface::class);
        $this->scopeConfigInterface = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->link = $this->objectManagerHelper->getObject(
            \Magento\Sales\Block\Adminhtml\Rss\Order\Grid\Link::class,
            [
                'context' => $this->context,
                'rssUrlBuilder' => $this->urlBuilderInterface,
                'scopeConfig' => $this->scopeConfigInterface
            ]
        );
    }

    public function testGetLink()
    {
        $link = 'http://magento.com/backend/rss/feed/index/type/new_order';
        $this->urlBuilderInterface->expects($this->once())->method('getUrl')
            ->with(['type' => 'new_order'])
            ->will($this->returnValue($link));
        $this->assertEquals($link, $this->link->getLink());
    }

    public function testGetLabel()
    {
        $this->assertEquals('New Order RSS', $this->link->getLabel());
    }

    public function testIsRssAllowed()
    {
        $this->assertTrue($this->link->isRssAllowed());
    }

    public function getFeeds()
    {
        $this->assertEmpty($this->link->getFeeds());
    }
}
