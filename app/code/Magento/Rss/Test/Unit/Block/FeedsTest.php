<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class FeedsTest
 * @package Magento\Rss\Block
 */
class FeedsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Rss\Block\Feeds
     */
    protected $block;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Rss\RssManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rssManagerInterface;

    protected function setUp()
    {
        $this->context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->rssManagerInterface = $this->createMock(\Magento\Framework\App\Rss\RssManagerInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $this->objectManagerHelper->getObject(
            \Magento\Rss\Block\Feeds::class,
            [
                'context' => $this->context,
                'rssManager' => $this->rssManagerInterface
            ]
        );
    }

    public function testGetFeeds()
    {
        $provider1 = $this->createMock(\Magento\Framework\App\Rss\DataProviderInterface::class);
        $provider2 = $this->createMock(\Magento\Framework\App\Rss\DataProviderInterface::class);
        $feed1 = [
            'group' => 'Some Group',
            'feeds' => [
                ['link' => 'feed 1 link', 'label' => 'Feed 1 Label'],
            ],
        ];
        $feed2 = ['link' => 'feed 2 link', 'label' => 'Feed 2 Label'];
        $provider1->expects($this->once())->method('getFeeds')->will($this->returnValue($feed1));
        $provider2->expects($this->once())->method('getFeeds')->will($this->returnValue($feed2));
        $this->rssManagerInterface->expects($this->once())->method('getProviders')
            ->will($this->returnValue([$provider1, $provider2]));

        $this->assertEquals([$feed2, $feed1], $this->block->getFeeds());
    }
}
