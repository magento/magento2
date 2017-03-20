<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Block\Adminhtml\Rss\Grid;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class LinkTest
 * @package Magento\Review\Block\Adminhtml\Rss\Grid
 */
class LinkTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderInterface;

    protected function setUp()
    {
        $this->urlBuilderInterface = $this->getMock(\Magento\Framework\App\Rss\UrlBuilderInterface::class);

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
        $this->urlBuilderInterface->expects($this->once())->method('getUrl')->will($this->returnValue($rssUrl));
        $this->assertEquals($rssUrl, $this->link->getLink());
    }

    public function testGetLabel()
    {
        $this->assertEquals('Pending Reviews RSS', $this->link->getLabel());
    }

    public function testIsRssAllowed()
    {
        $this->assertEquals(true, $this->link->isRssAllowed());
    }
}
