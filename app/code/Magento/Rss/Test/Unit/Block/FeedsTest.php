<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class FeedsTest
 * @package Magento\Rss\Block
 */
class FeedsTest extends \PHPUnit_Framework_TestCase
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
        $this->context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $this->rssManagerInterface = $this->getMock('Magento\Framework\App\Rss\RssManagerInterface');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $this->objectManagerHelper->getObject(
            'Magento\Rss\Block\Feeds',
            [
                'context' => $this->context,
                'rssManager' => $this->rssManagerInterface
            ]
        );
    }

    public function testGetFeeds()
    {
        $provider1 = $this->getMock('\Magento\Framework\App\Rss\DataProviderInterface');
        $provider2 = $this->getMock('\Magento\Framework\App\Rss\DataProviderInterface');
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
