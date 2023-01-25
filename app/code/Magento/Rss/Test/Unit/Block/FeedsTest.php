<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Rss\Test\Unit\Block;

use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\App\Rss\RssManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Rss\Block\Feeds;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FeedsTest extends TestCase
{
    /**
     * @var Feeds
     */
    protected $block;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var RssManagerInterface|MockObject
     */
    protected $rssManagerInterface;

    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->rssManagerInterface = $this->getMockForAbstractClass(RssManagerInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $this->objectManagerHelper->getObject(
            Feeds::class,
            [
                'context' => $this->context,
                'rssManager' => $this->rssManagerInterface
            ]
        );
    }

    public function testGetFeeds()
    {
        $provider1 = $this->getMockForAbstractClass(DataProviderInterface::class);
        $provider2 = $this->getMockForAbstractClass(DataProviderInterface::class);
        $feed1 = [
            'group' => 'Some Group',
            'feeds' => [
                ['link' => 'feed 1 link', 'label' => 'Feed 1 Label'],
            ],
        ];
        $feed2 = ['link' => 'feed 2 link', 'label' => 'Feed 2 Label'];
        $provider1->expects($this->once())->method('getFeeds')->willReturn($feed1);
        $provider2->expects($this->once())->method('getFeeds')->willReturn($feed2);
        $this->rssManagerInterface->expects($this->once())->method('getProviders')
            ->willReturn([$provider1, $provider2]);

        $this->assertEquals([$feed2, $feed1], $this->block->getFeeds());
    }
}
