<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Shipping\Helper\Data
     */
    protected $_helper = null;

    protected function setUp()
    {
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Shipping\Helper\Data'
        );
    }

    /**
     * @param string $modelName
     * @param string $getIdMethod
     * @param int $entityId
     * @param string $code
     * @param string $expected
     * @dataProvider getTrackingPopupUrlBySalesModelDataProvider
     */
    public function testGetTrackingPopupUrlBySalesModel($modelName, $getIdMethod, $entityId, $code, $expected)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $constructArgs = [];
        if ('Magento\Sales\Model\Order\Shipment' == $modelName) {
            $orderRepository = $this->_getMockOrderRepository($code);
            $constructArgs['orderRepository'] = $orderRepository;
        } elseif ('Magento\Sales\Model\Order\Shipment\Track' == $modelName) {
            $shipmentRepository = $this->_getMockShipmentRepository($code);
            $constructArgs['shipmentRepository'] = $shipmentRepository;
        }

        $model = $objectManager->create($modelName, $constructArgs);
        $model->{$getIdMethod}($entityId);

        if ('Magento\Sales\Model\Order' == $modelName) {
            $model->setProtectCode($code);
        }
        if ('Magento\Sales\Model\Order\Shipment\Track' == $modelName) {
            $model->setParentId(1);
        }

        $actual = $this->_helper->getTrackingPopupUrlBySalesModel($model);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @param $code
     * @return \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected function _getMockOrderRepository($code)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $order = $objectManager->create('Magento\Sales\Model\Order');
        $order->setProtectCode($code);
        $orderRepository = $this->getMock('Magento\Sales\Api\OrderRepositoryInterface', [], [], '', false);
        $orderRepository->expects($this->atLeastOnce())->method('get')->will($this->returnValue($order));
        return $orderRepository;
    }

    /**
     * @param $code
     * @return \Magento\Sales\Model\Order\ShipmentRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getMockShipmentRepository($code)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $orderRepository = $this->_getMockOrderRepository($code);
        $shipmentArgs = ['orderRepository' => $orderRepository];

        $shipment = $objectManager->create('Magento\Sales\Model\Order\Shipment', $shipmentArgs);
        $shipmentRepository = $this->getMock(
            'Magento\Sales\Model\Order\ShipmentRepository',
            ['get'],
            [],
            '',
            false
        );
        $shipmentRepository->expects($this->atLeastOnce())->method('get')->willReturn($shipment);
        return $shipmentRepository;
    }

    /**
     * @return array
     */
    public function getTrackingPopupUrlBySalesModelDataProvider()
    {
        return [
            [
                'Magento\Sales\Model\Order',
                'setId',
                42,
                'abc',
                'http://localhost/index.php/shipping/tracking/popup?hash=b3JkZXJfaWQ6NDI6YWJj',
            ],
            [
                'Magento\Sales\Model\Order\Shipment',
                'setId',
                42,
                'abc',
                'http://localhost/index.php/shipping/tracking/popup?hash=c2hpcF9pZDo0MjphYmM%2C'
            ],
            [
                'Magento\Sales\Model\Order\Shipment\Track',
                'setEntityId',
                42,
                'abc',
                'http://localhost/index.php/shipping/tracking/popup?hash=dHJhY2tfaWQ6NDI6YWJj'
            ]
        ];
    }
}
