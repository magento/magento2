<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Block\Adminhtml\Rss\Grid;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class LinkTest
 * @package Magento\Review\Block\Adminhtml\Rss\Grid
 */
class LinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Review\Block\Adminhtml\Rss\Grid\Link
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

    protected function setUp(): void
    {
        $this->urlBuilderInterface = $this->createMock(\Magento\Framework\App\Rss\UrlBuilderInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->link = $this->objectManagerHelper->getObject(
            \Magento\Review\Block\Adminhtml\Rss\Grid\Link::class,
            [
                'rssUrlBuilder' => $this->urlBuilderInterface
            ]
        );
    }

    public function testGetLink()
    {
        $rssUrl = 'http://rss.magento.com';
        $this->urlBuilderInterface->expects($this->once())->method('getUrl')->willReturn($rssUrl);
        $this->assertEquals($rssUrl, $this->link->getLink());
    }

    public function testGetLabel()
    {
        $this->assertEquals('Pending Reviews RSS', $this->link->getLabel());
    }

    public function testIsRssAllowed()
    {
        $this->assertTrue($this->link->isRssAllowed());
    }
}
