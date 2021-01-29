<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Shipment;

class TrackTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Shipment\Track
     */
    protected $_model;

    protected function setUp(): void
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = [
            'shipmentRepository' => $this->createMock(\Magento\Sales\Model\Order\ShipmentRepository::class),
        ];

        $this->_model = $objectManagerHelper->getObject(\Magento\Sales\Model\Order\Shipment\Track::class, $arguments);
    }

    public function testAddData()
    {
        $number = 123;
        $this->assertNull($this->_model->getTrackNumber());
        $this->_model->addData(['number' => $number, 'test' => true]);

        $this->assertTrue($this->_model->getTest());
        $this->assertEquals($number, $this->_model->getTrackNumber());
    }

    public function testGetStoreId()
    {
        $storeId = 10;
        $storeObject = new \Magento\Framework\DataObject(['id' => $storeId]);

        $shipmentMock = $this->createPartialMock(\Magento\Sales\Model\Order\Shipment::class, ['getStore', '__wakeup']);
        $shipmentMock->expects($this->once())->method('getStore')->willReturn($storeObject);

        $this->_model->setShipment($shipmentMock);
        $this->assertEquals($storeId, $this->_model->getStoreId());
    }

    public function testSetGetNumber()
    {
        $this->assertNull($this->_model->getNumber());
        $this->assertNull($this->_model->getTrackNumber());

        $this->_model->setNumber('test');

        $this->assertEquals('test', $this->_model->getNumber());
        $this->assertEquals('test', $this->_model->getTrackNumber());
    }

    /**
     * @dataProvider isCustomDataProvider
     * @param bool $expectedResult
     * @param string $carrierCodeToSet
     */
    public function testIsCustom($expectedResult, $carrierCodeToSet)
    {
        $this->_model->setCarrierCode($carrierCodeToSet);
        $this->assertEquals($expectedResult, $this->_model->isCustom());
    }

    /**
     * @return array
     */
    public static function isCustomDataProvider()
    {
        return [[true, \Magento\Sales\Model\Order\Shipment\Track::CUSTOM_CARRIER_CODE], [false, 'ups']];
    }
}
