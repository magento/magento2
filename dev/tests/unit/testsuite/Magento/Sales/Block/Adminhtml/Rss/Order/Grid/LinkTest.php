<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Block\Adminhtml\Rss\Order\Grid;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class LinkTest
 * @package Magento\Sales\Block\Adminhtml\Rss\Order\Grid
 */
class LinkTest extends \PHPUnit_Framework_TestCase
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
        $this->context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $this->urlBuilderInterface = $this->getMock('Magento\Framework\App\Rss\UrlBuilderInterface');
        $this->scopeConfigInterface = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->link = $this->objectManagerHelper->getObject(
            'Magento\Sales\Block\Adminhtml\Rss\Order\Grid\Link',
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
            ->with(array('type' => 'new_order'))
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
