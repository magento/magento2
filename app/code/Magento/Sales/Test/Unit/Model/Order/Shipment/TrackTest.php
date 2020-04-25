<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Shipment;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\Order\ShipmentRepository;
use PHPUnit\Framework\TestCase;

class TrackTest extends TestCase
{
    /**
     * @var Track
     */
    protected $_model;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $arguments = [
            'shipmentRepository' => $this->createMock(ShipmentRepository::class),
        ];

        $this->_model = $objectManagerHelper->getObject(Track::class, $arguments);
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
        $storeObject = new DataObject(['id' => $storeId]);

        $shipmentMock = $this->createPartialMock(Shipment::class, ['getStore']);
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
        return [[true, Track::CUSTOM_CARRIER_CODE], [false, 'ups']];
    }
}
