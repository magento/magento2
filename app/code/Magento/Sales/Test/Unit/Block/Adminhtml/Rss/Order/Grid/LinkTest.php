<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Rss\Order\Grid;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Adminhtml\Rss\Order\Grid\Link;
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
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var UrlBuilderInterface|MockObject
     */
    protected $urlBuilderInterface;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigInterface;

    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->urlBuilderInterface = $this->getMockForAbstractClass(UrlBuilderInterface::class);
        $this->scopeConfigInterface = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->link = $this->objectManagerHelper->getObject(
            Link::class,
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
            ->willReturn($link);
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
