<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

class TrackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Shipment\Track
     */
    protected $_model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = [
            'shipmentFactory' => $this->getMock(
                'Magento\Sales\Model\Order\ShipmentFactory',
                [],
                [],
                '',
                false
            ),
        ];

        $this->_model = $objectManagerHelper->getObject('Magento\Sales\Model\Order\Shipment\Track', $arguments);
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
        $storeObject = new \Magento\Framework\Object(['id' => $storeId]);

        $shipmentMock = $this->getMock(
            'Magento\Sales\Model\Order\Shipment',
            ['getStore', '__wakeup'],
            [],
            '',
            false
        );
        $shipmentMock->expects($this->once())->method('getStore')->will($this->returnValue($storeObject));

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
