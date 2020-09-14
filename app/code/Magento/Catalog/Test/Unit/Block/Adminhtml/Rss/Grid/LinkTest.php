<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Rss\Grid;

use Magento\Catalog\Block\Adminhtml\Rss\Grid\Link;
use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
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

    protected function setUp(): void
    {
        $this->urlBuilderInterface = $this->getMockForAbstractClass(UrlBuilderInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->link = $this->objectManagerHelper->getObject(
            Link::class,
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
        $this->assertEquals('Notify Low Stock RSS', $this->link->getLabel());
    }

    public function testIsRssAllowed()
    {
        $this->assertTrue($this->link->isRssAllowed());
    }
}
