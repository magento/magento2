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
namespace Magento\Sales\Model\Rss;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class OrderStatusTest
 * @package Magento\Sales\Model\Rss
 */
class OrderStatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Rss\OrderStatus
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlInterface;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestInterface;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderStatusFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $timezoneInterface;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $order;
    /**
     * @var array
     */
    protected $feedData = array(
        'title' => 'Order # 100000001 Notification(s)',
        'link' => 'http://magento.com/sales/order/view/order_id/1',
        'description' => 'Order # 100000001 Notification(s)',
        'charset' => 'UTF-8',
        'entries' => array(
            array(
                'title' => 'Details for Order #100000001',
                'link' => 'http://magento.com/sales/order/view/order_id/1',
                'description' => '<p>Notified Date: <br/>Comment: Some comment<br/></p>'
            ),
            array(
                'title' => 'Order #100000001 created at ',
                'link' => 'http://magento.com/sales/order/view/order_id/1',
                'description' => '<p>Current Status: Pending<br/>Total: 15.00<br/></p>'
            ),
        )
    );

    protected function setUp()
    {
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManager');
        $this->urlInterface = $this->getMock('Magento\Framework\UrlInterface');
        $this->requestInterface = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->orderStatusFactory = $this->getMockBuilder('Magento\Sales\Model\Resource\Order\Rss\OrderStatusFactory')
            ->setMethods(array('create'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->timezoneInterface = $this->getMock('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $this->orderFactory = $this->getMock('Magento\Sales\Model\OrderFactory', ['create'], [], '', false);
        $this->scopeConfigInterface = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $this->order = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->setMethods([
                '__sleep',
                '__wakeup',
                'getIncrementId',
                'getId',
                'getCustomerId',
                'load',
                'getStatusLabel',
                'formatPrice',
                'getGrandTotal'
            ])->disableOriginalConstructor()->getMock();
        $this->order->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->order->expects($this->any())->method('getIncrementId')->will($this->returnValue('100000001'));
        $this->order->expects($this->any())->method('getCustomerId')->will($this->returnValue(1));
        $this->order->expects($this->any())->method('getStatusLabel')->will($this->returnValue('Pending'));
        $this->order->expects($this->any())->method('formatPrice')->will($this->returnValue('15.00'));
        $this->order->expects($this->any())->method('getGrandTotal')->will($this->returnValue(15));
        $this->order->expects($this->any())->method('load')->with(1)->will($this->returnSelf());

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Sales\Model\Rss\OrderStatus',
            [
                'objectManager' => $this->objectManager,
                'urlBuilder' => $this->urlInterface,
                'request' => $this->requestInterface,
                'orderResourceFactory' => $this->orderStatusFactory,
                'localeDate' => $this->timezoneInterface,
                'orderFactory' => $this->orderFactory,
                'scopeConfig' => $this->scopeConfigInterface
            ]
        );
    }
    public function testGetData()
    {
        $this->orderFactory->expects($this->once())->method('create')->will($this->returnValue($this->order));
        $this->requestInterface->expects($this->any())->method('getParam')
            ->with('data')
            ->will($this->returnValue('eyJvcmRlcl9pZCI6MSwiaW5jcmVtZW50X2lkIjoiMTAwMDAwMDAxIiwiY3VzdG9tZXJfaWQiOjF9'));
        $resource = $this->getMockBuilder('\Magento\Sales\Model\Resource\Order\Rss\OrderStatus')
            ->setMethods(['getAllCommentCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $comment = array(
            'entity_type_code' => 'order',
            'increment_id' => '100000001',
            'created_at' => '2014-10-09 18:25:50',
            'comment' => 'Some comment'
        );
        $resource->expects($this->once())->method('getAllCommentCollection')->will($this->returnValue(array($comment)));
        $this->orderStatusFactory->expects($this->once())->method('create')->will($this->returnValue($resource));
        $this->urlInterface->expects($this->any())->method('getUrl')
            ->with('sales/order/view', array('order_id' => 1))
            ->will($this->returnValue('http://magento.com/sales/order/view/order_id/1'));

        $this->assertEquals($this->feedData, $this->model->getRssData());
    }

    public function testIsAllowed()
    {
        $this->scopeConfigInterface->expects($this->once())->method('getValue')
            ->with('rss/order/status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue(true));
        $this->assertTrue($this->model->isAllowed());
    }

    public function testGetCacheKey()
    {
        $this->requestInterface->expects($this->any())->method('getParam')
            ->with('data')
            ->will($this->returnValue('eyJvcmRlcl9pZCI6MSwiaW5jcmVtZW50X2lkIjoiMTAwMDAwMDAxIiwiY3VzdG9tZXJfaWQiOjF9'));
        $this->orderFactory->expects($this->once())->method('create')->will($this->returnValue($this->order));
        $this->assertEquals('rss_order_status_data_' . md5('11000000011'), $this->model->getCacheKey());
    }

    public function testGetCacheLifetime()
    {
        $this->assertEquals(600, $this->model->getCacheLifetime());
    }
}
