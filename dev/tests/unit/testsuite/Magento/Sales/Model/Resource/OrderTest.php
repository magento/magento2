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
namespace Magento\Sales\Model\Resource;

/**
 * Class OrderTest
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\Order
     */
    protected $resource;
    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;
    /**
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTimeMock;
    /**
     * @var \Magento\Sales\Model\Resource\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMock;
    /**
     * @var \Magento\Sales\Model\Resource\Order\Handler\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressHandlerMock;
    /**
     * @var \Magento\Sales\Model\Resource\Order\Handler\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stateHandlerMock;
    /**
     * @var \Magento\Sales\Model\Increment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salesIncrementMock;
    /**
     * @var \Magento\Sales\Model\Resource\Order\Grid|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $gridAggregatorMock;
    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;
    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;
    /**
     * @var \Magento\Store\Model\Group|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeGroupMock;
    /**
     * \Magento\Sales\Model\Website|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteMock;
    /**
     * @var \Magento\Customer\Model\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;
    /**
     * @var \Magento\Sales\Model\Resource\Order\Item\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderItemCollectionMock;
    /**
     * @var \Magento\Sales\Model\Resource\Order\Payment\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderPaymentCollectionMock;
    /**
     * @var \Magento\Sales\Model\Resource\Order\Status\History\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderStatusHistoryCollectionMock;
    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterMock;

    /**
     * Mock class dependencies
     */
    public function setUp()
    {
        $this->resourceMock = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
        $this->dateTimeMock = $this->getMock('Magento\Framework\Stdlib\DateTime', [], [], '', false);
        $this->attributeMock = $this->getMock('Magento\Sales\Model\Resource\Attribute', [], [], '', false);
        $this->addressHandlerMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Handler\Address',
            [],
            [],
            '',
            false
        );
        $this->stateHandlerMock = $this->getMock('Magento\Sales\Model\Resource\Order\Handler\State', [], [], '', false);
        $this->salesIncrementMock = $this->getMock('Magento\Sales\Model\Increment', [], [], '', false);
        $this->gridAggregatorMock = $this->getMock('Magento\Sales\Model\Resource\Order\Grid', [], [], '', false);
        $this->orderMock = $this->getMock(
            'Magento\Sales\Model\Order',
            [
                '__wakeup', 'getId', 'getStore', 'getGroup', 'getName', 'setStoreName', 'setTotalItemCount', 'setData',
                'getCustomerId', 'getCustomer', 'setCustomerId', 'getItemsCollection', 'getPaymentsCollection',
                'getStatusHistoryCollection', 'getRelatedObjects', 'save'
            ],
            [],
            '',
            false
        );
        $this->storeMock = $this->getMock('Magento\Store\Model\Store', ['__wakeup'], [], '', false);
        $this->storeGroupMock = $this->getMock('Magento\Store\Model\Group', ['__wakeup'], [], '', false);
        $this->websiteMock = $this->getMock('Magento\Sales\Model\Website', ['__wakeup'], [], '', false);
        $this->customerMock = $this->getMock('Magento\Customer\Model\Customer', ['__wakeup'], [], '', false);
        $this->orderItemCollectionMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Item\Collection',
            [],
            [],
            '',
            false
        );
        $this->orderPaymentCollectionMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Payment\Collection',
            [],
            [],
            '',
            false
        );
        $this->orderStatusHistoryCollectionMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Status\History\Collection',
            [],
            [],
            '',
            false
        );
        $this->adapterMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            [
                'describeTable',
                'insert',
                'lastInsertId',
                'beginTransaction',
                'rollback',
                'commit',
                'quoteInto',
                'update'
            ],
            [],
            '',
            false
        );

        $this->resource = new Order(
            $this->resourceMock,
            $this->dateTimeMock,
            $this->attributeMock,
            $this->salesIncrementMock,
            $this->addressHandlerMock,
            $this->stateHandlerMock,
            $this->gridAggregatorMock
        );
    }

    public function testSave()
    {
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->adapterMock);
        $this->adapterMock->expects($this->any())
            ->method('quoteInto');
        $this->adapterMock->expects($this->any())
            ->method('describeTable')
            ->will($this->returnValue([]));
        $this->adapterMock->expects($this->any())
            ->method('update');
        $this->adapterMock->expects($this->any())
            ->method('lastInsertId');
        $this->addressHandlerMock->expects($this->once())
            ->method('removeEmptyAddresses')
            ->with($this->equalTo($this->orderMock))
            ->will($this->returnSelf());
        $this->stateHandlerMock->expects($this->once())
            ->method('check')
            ->with($this->equalTo($this->orderMock))
            ->will($this->returnSelf());
        $this->orderMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->orderMock->expects($this->once())
            ->method('getRelatedObjects')
            ->willReturn([]);
        $this->resource->save($this->orderMock);
    }
}
