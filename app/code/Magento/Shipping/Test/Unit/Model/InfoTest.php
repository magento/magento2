<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Shipping\Model\Info;
use Magento\Shipping\Model\ResourceModel\Order\Track\CollectionFactory;

/**
 * Test for \Magento\Shipping\Model\Info.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Info
     */
    private $info;

    /**
     * @var \Magento\Shipping\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helper;

    /**
     * @var \Magento\Sales\Model\OrderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderFactory;

    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shipmentRepository;

    /**
     * @var \Magento\Shipping\Model\Order\TrackFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $trackFactory;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $trackCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->helper = $this->getMockBuilder(\Magento\Shipping\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderFactory = $this->getMockBuilder(\Magento\Sales\Model\OrderFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->shipmentRepository = $this->getMockBuilder(\Magento\Sales\Api\ShipmentRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->trackFactory = $this->getMockBuilder(\Magento\Shipping\Model\Order\TrackFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->trackCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $objectManagerHelper = new ObjectManager($this);
        $this->info = $objectManagerHelper->getObject(
            Info::class,
            [
                'shippingData' => $this->helper,
                'orderFactory' => $this->orderFactory,
                'shipmentRepository' => $this->shipmentRepository,
                'trackFactory' => $this->trackFactory,
                'trackCollectionFactory' => $this->trackCollectionFactory,
            ]
        );
    }

    public function testLoadByHashWithOrderId()
    {
        $hash = strtr(base64_encode('order_id:1:protected_code'), '+/=', '-_,');
        $decodedHash = [
            'key' => 'order_id',
            'id' => 1,
            'hash' => 'protected_code',
        ];
        $shipmentId = 1;
        $shipmentIncrementId = 3;
        $trackDetails = 'track_details';

        $this->helper->expects($this->atLeastOnce())
            ->method('decodeTrackingHash')
            ->with($hash)
            ->willReturn($decodedHash);
        $shipmentCollection = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Shipment\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIterator'])
            ->getMock();
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'getProtectCode', 'getShipmentsCollection'])
            ->getMock();
        $order->expects($this->atLeastOnce())->method('load')->with($decodedHash['id'])->willReturnSelf();
        $order->expects($this->atLeastOnce())->method('getId')->willReturn($decodedHash['id']);
        $order->expects($this->atLeastOnce())->method('getProtectCode')->willReturn($decodedHash['hash']);
        $order->expects($this->atLeastOnce())->method('getShipmentsCollection')->willReturn($shipmentCollection);

        $shipment = $this->getMockBuilder(\Magento\Sales\Model\Order\Shipment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIncrementId', 'getId'])
            ->getMock();
        $shipment->expects($this->atLeastOnce())->method('getIncrementId')->willReturn($shipmentIncrementId);
        $shipment->expects($this->atLeastOnce())->method('getId')->willReturn($shipmentId);

        $shipmentCollection->expects($this->any())->method('getIterator')->willReturn(new \ArrayIterator([$shipment]));
        $this->orderFactory->expects($this->atLeastOnce())->method('create')->willReturn($order);
        $track = $this->getMockBuilder(\Magento\Sales\Model\Order\Shipment\Track::class)
            ->disableOriginalConstructor()
            ->setMethods(['setShipment', 'getNumberDetail'])
            ->getMock();
        $track->expects($this->atLeastOnce())->method('setShipment')->with($shipment)->willReturnSelf();
        $track->expects($this->atLeastOnce())->method('getNumberDetail')->willReturn($trackDetails);
        $trackCollection = $this->getMockBuilder(\Magento\Shipping\Model\ResourceModel\Order\Track\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIterator', 'setShipmentFilter'])
            ->getMock();
        $trackCollection->expects($this->atLeastOnce())
            ->method('setShipmentFilter')
            ->with($shipmentId)
            ->willReturnSelf();
        $trackCollection->expects($this->atLeastOnce())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$track]));
        $this->trackCollectionFactory->expects($this->atLeastOnce())->method('create')->willReturn($trackCollection);

        $this->info->loadByHash($hash);
        $this->assertEquals([$shipmentIncrementId => [$trackDetails]], $this->info->getTrackingInfo());
    }

    public function testLoadByHashWithOrderIdWrongCode()
    {
        $hash = strtr(base64_encode('order_id:1:0'), '+/=', '-_,');
        $decodedHash = [
            'key' => 'order_id',
            'id' => 1,
            'hash' => '0',
        ];

        $this->helper->expects($this->atLeastOnce())
            ->method('decodeTrackingHash')
            ->with($hash)
            ->willReturn($decodedHash);
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'getProtectCode', 'getShipmentsCollection'])
            ->getMock();
        $order->expects($this->atLeastOnce())->method('load')->with($decodedHash['id'])->willReturnSelf();
        $order->expects($this->atLeastOnce())->method('getId')->willReturn($decodedHash['id']);
        $order->expects($this->atLeastOnce())->method('getProtectCode')->willReturn('0e123123123');
        $this->orderFactory->expects($this->atLeastOnce())->method('create')->willReturn($order);
        $this->info->loadByHash($hash);
        $this->assertEmpty($this->info->getTrackingInfo());
    }

    public function testLoadByHashWithShipmentId()
    {
        $hash = strtr(base64_encode('ship_id:1:protected_code'), '+/=', '-_,');
        $decodedHash = [
            'key' => 'ship_id',
            'id' => 1,
            'hash' => 'protected_code',
        ];
        $shipmentIncrementId = 3;
        $trackDetails = 'track_details';

        $this->helper->expects($this->atLeastOnce())
            ->method('decodeTrackingHash')
            ->with($hash)
            ->willReturn($decodedHash);
        $shipment = $this->getMockBuilder(\Magento\Sales\Model\Order\Shipment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityId', 'getProtectCode', 'getIncrementId', 'getId'])
            ->getMock();
        $shipment->expects($this->atLeastOnce())->method('getIncrementId')->willReturn($shipmentIncrementId);
        $shipment->expects($this->atLeastOnce())->method('getId')->willReturn($decodedHash['id']);
        $shipment->expects($this->atLeastOnce())->method('getEntityId')->willReturn(3);
        $shipment->expects($this->atLeastOnce())->method('getProtectCode')->willReturn($decodedHash['hash']);
        $this->shipmentRepository->expects($this->atLeastOnce())
            ->method('get')
            ->with($decodedHash['id'])
            ->willReturn($shipment);
        $track = $this->getMockBuilder(\Magento\Sales\Model\Order\Shipment\Track::class)
            ->disableOriginalConstructor()
            ->setMethods(['setShipment', 'getNumberDetail'])
            ->getMock();
        $track->expects($this->atLeastOnce())->method('setShipment')->with($shipment)->willReturnSelf();
        $track->expects($this->atLeastOnce())->method('getNumberDetail')->willReturn($trackDetails);
        $trackCollection = $this->getMockBuilder(\Magento\Shipping\Model\ResourceModel\Order\Track\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIterator', 'setShipmentFilter'])
            ->getMock();
        $trackCollection->expects($this->atLeastOnce())
            ->method('setShipmentFilter')
            ->with($decodedHash['id'])
            ->willReturnSelf();
        $trackCollection->expects($this->atLeastOnce())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$track]));
        $this->trackCollectionFactory->expects($this->atLeastOnce())->method('create')->willReturn($trackCollection);

        $this->info->loadByHash($hash);
        $this->assertEquals([$shipmentIncrementId => [$trackDetails]], $this->info->getTrackingInfo());
    }

    public function testLoadByHashWithShipmentIdWrongCode()
    {
        $hash = strtr(base64_encode('ship_id:1:0'), '+/=', '-_,');
        $decodedHash = [
            'key' => 'ship_id',
            'id' => 1,
            'hash' => '0',
        ];

        $this->helper->expects($this->atLeastOnce())
            ->method('decodeTrackingHash')
            ->with($hash)
            ->willReturn($decodedHash);
        $shipment = $this->getMockBuilder(\Magento\Sales\Model\Order\Shipment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityId', 'getProtectCode', 'getIncrementId', 'getId'])
            ->getMock();
        $shipment->expects($this->atLeastOnce())->method('getEntityId')->willReturn(3);
        $shipment->expects($this->atLeastOnce())->method('getProtectCode')->willReturn('0e123123123');
        $this->shipmentRepository->expects($this->atLeastOnce())
            ->method('get')
            ->with($decodedHash['id'])
            ->willReturn($shipment);

        $this->info->loadByHash($hash);
        $this->assertEmpty($this->info->getTrackingInfo());
    }

    public function testLoadByHashWithTrackId()
    {
        $hash = base64_encode('hash');
        $decodedHash = [
            'key' => 'track_id',
            'id' => 1,
            'hash' => 'protected_code',
        ];
        $trackDetails = 'track_details';
        $this->helper->expects($this->atLeastOnce())
            ->method('decodeTrackingHash')
            ->with($hash)
            ->willReturn($decodedHash);
        $track = $this->getMockBuilder(\Magento\Sales\Model\Order\Shipment\Track::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'getProtectCode', 'getNumberDetail'])
            ->getMock();
        $track->expects($this->atLeastOnce())->method('load')->with($decodedHash['id'])->willReturnSelf();
        $track->expects($this->atLeastOnce())->method('getId')->willReturn($decodedHash['id']);
        $track->expects($this->atLeastOnce())->method('getProtectCode')->willReturn($decodedHash['hash']);
        $track->expects($this->atLeastOnce())->method('getNumberDetail')->willReturn($trackDetails);
        $this->trackFactory->expects($this->atLeastOnce())->method('create')->willReturn($track);

        $this->info->loadByHash($hash);
        $this->assertEquals([[$trackDetails]], $this->info->getTrackingInfo());
    }

    public function testLoadByHashWithWrongCode()
    {
        $hash = base64_encode('hash');
        $decodedHash = [
            'key' => 'track_id',
            'id' => 1,
            'hash' => 'protected_code',
        ];
        $this->helper->expects($this->atLeastOnce())
            ->method('decodeTrackingHash')
            ->with($hash)
            ->willReturn($decodedHash);
        $track = $this->getMockBuilder(\Magento\Sales\Model\Order\Shipment\Track::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'getProtectCode', 'getNumberDetail'])
            ->getMock();
        $track->expects($this->atLeastOnce())->method('load')->with($decodedHash['id'])->willReturnSelf();
        $track->expects($this->atLeastOnce())->method('getId')->willReturn($decodedHash['id']);
        $track->expects($this->atLeastOnce())->method('getProtectCode')->willReturn('0e123123123');
        $this->trackFactory->expects($this->atLeastOnce())->method('create')->willReturn($track);

        $this->info->loadByHash($hash);
        $this->assertEmpty($this->info->getTrackingInfo());
    }
}
