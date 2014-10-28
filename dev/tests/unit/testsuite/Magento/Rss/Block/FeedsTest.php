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
namespace Magento\Rss\Block;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

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
        $feed1 = array(
            'group' => 'Some Group',
            'feeds' => array(
                array('link' => 'feed 1 link', 'label' => 'Feed 1 Label')
            )
        );
        $feed2 = array('link' => 'feed 2 link', 'label' => 'Feed 2 Label');
        $provider1->expects($this->once())->method('getFeeds')->will($this->returnValue($feed1));
        $provider2->expects($this->once())->method('getFeeds')->will($this->returnValue($feed2));
        $this->rssManagerInterface->expects($this->once())->method('getProviders')
            ->will($this->returnValue(array($provider1, $provider2)));

        $this->assertEquals(array($feed2, $feed1), $this->block->getFeeds());
    }
}
