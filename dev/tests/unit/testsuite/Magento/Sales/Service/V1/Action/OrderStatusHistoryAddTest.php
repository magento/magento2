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
namespace Magento\Sales\Service\V1\Action;

use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Order\Status\HistoryConverter;

/**
 * Class OrderStatusHistoryAddTest
 * @package Magento\Sales\Service\V1
 */
class OrderStatusHistoryAddTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Action\OrderStatusHistoryAdd
     */
    protected $service;

    /**
     * @var OrderRepository | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderRepositoryMock;

    /**
     * @var HistoryConverter | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $historyConverterMock;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $this->orderRepositoryMock = $this->getMock(
            'Magento\Sales\Model\OrderRepository',
            ['get'],
            [],
            '',
            false
        );
        $this->historyConverterMock = $this->getMock(
            'Magento\Sales\Model\Order\Status\HistoryConverter',
            ['getModel'],
            [],
            '',
            false
        );
        $this->service = new OrderStatusHistoryAdd(
            $this->orderRepositoryMock,
            $this->historyConverterMock
        );
    }

    public function testInvoke()
    {
        $id = 1;

        $dataObject = $this->getMock('Magento\Sales\Service\V1\Data\OrderStatusHistory', [], [], '', false);
        $model = $this->getMock('Magento\Sales\Model\Order\Status\History', [], [], '', false);
        $this->historyConverterMock->expects($this->once())
            ->method('getModel')
            ->with($dataObject)
            ->will($this->returnValue($model));
        $orderMock = $this->getMock('Magento\Sales\Model\Order', [], [], '', false);
        $orderMock->expects($this->once())
            ->method('addStatusHistory')
            ->with($model);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($id)
            ->will($this->returnValue($orderMock));

        $this->assertTrue($this->service->invoke($id, $dataObject));
    }
}
