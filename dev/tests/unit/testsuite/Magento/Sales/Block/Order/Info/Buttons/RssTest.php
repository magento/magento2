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
namespace Magento\Sales\Block\Order\Info\Buttons;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class RssTest
 * @package Magento\Sales\Block\Order\Info\Buttons
 */
class RssTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Block\Order\Info\Buttons\Rss
     */
    protected $rss;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactory;

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
        $this->orderFactory = $this->getMock('Magento\Sales\Model\OrderFactory', ['create'], [], '', false);
        $this->urlBuilderInterface = $this->getMock('Magento\Framework\App\Rss\UrlBuilderInterface');
        $this->scopeConfigInterface = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $request = $this->getMock('Magento\Framework\App\RequestInterface');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->rss = $this->objectManagerHelper->getObject(
            'Magento\Sales\Block\Order\Info\Buttons\Rss',
            [
                'request' => $request,
                'orderFactory' => $this->orderFactory,
                'rssUrlBuilder' => $this->urlBuilderInterface,
                'scopeConfig' => $this->scopeConfigInterface
            ]
        );
    }

    public function testGetLink()
    {
        $order = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->setMethods(array('getId', 'getCustomerId', 'getIncrementId', 'load', '__wakeup', '__sleep'))
            ->disableOriginalConstructor()
            ->getMock();
        $order->expects($this->once())->method('load')->will($this->returnSelf());
        $order->expects($this->once())->method('getId')->will($this->returnValue(1));
        $order->expects($this->once())->method('getCustomerId')->will($this->returnValue(1));
        $order->expects($this->once())->method('getIncrementId')->will($this->returnValue('100000001'));

        $this->orderFactory->expects($this->once())->method('create')->will($this->returnValue($order));

        $data = base64_encode(json_encode(array('order_id' => 1, 'increment_id' => '100000001', 'customer_id' => 1, )));
        $link = 'http://magento.com/rss/feed/index/type/order_status?data=' . $data;
        $this->urlBuilderInterface->expects($this->once())->method('getUrl')
            ->with(array(
                'type' => 'order_status',
                '_secure' => true,
                '_query' => array('data' => $data)
            ))->will($this->returnValue($link));
        $this->assertEquals($link, $this->rss->getLink());
    }

    public function testGetLabel()
    {
        $this->assertEquals('Subscribe to Order Status', $this->rss->getLabel());
    }

    public function testIsRssAllowed()
    {
        $this->scopeConfigInterface->expects($this->once())->method('isSetFlag')
            ->with('rss/order/status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue(true));
        $this->assertTrue($this->rss->isRssAllowed());
    }
}
