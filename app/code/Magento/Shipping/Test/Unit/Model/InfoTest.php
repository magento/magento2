<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Collection;
use Magento\Shipping\Helper\Data;
use Magento\Shipping\Model\Info;
use Magento\Shipping\Model\Order\TrackFactory;
use Magento\Shipping\Model\ResourceModel\Order\Track\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Shipping\Model\Info.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InfoTest extends TestCase
{
    /**
     * @var Info
     */
    private $info;

    /**
     * @var Data|MockObject
     */
    private $helper;

    /**
     * @var OrderFactory|MockObject
     */
    private $orderFactory;

    /**
     * @var ShipmentRepositoryInterface|MockObject
     */
    private $shipmentRepository;

    /**
     * @var TrackFactory|MockObject
     */
    private $trackFactory;

    /**
     * @var CollectionFactory|MockObject
     */
    private $trackCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->helper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderFactory = $this->getMockBuilder(OrderFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->shipmentRepository = $this->getMockBuilder(ShipmentRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->trackFactory = $this->getMockBuilder(TrackFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->trackCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
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
        $shipmentCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getIterator'])
            ->getMock();

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getId', 'getProtectCode', 'getShipmentsCollection'])
            ->getMock();
        $order->expects($this->atLeastOnce())->method('load')->with($decodedHash['id'])->willReturnSelf();
        $order->expects($this->atLeastOnce())->method('getId')->willReturn($decodedHash['id']);
        $order->expects($this->atLeastOnce())->method('getProtectCode')->willReturn($decodedHash['hash']);
        $order->expects($this->atLeastOnce())->method('getShipmentsCollection')->willReturn($shipmentCollection);
        $this->orderFactory->expects($this->atLeastOnce())->method('create')->willReturn($order);

        $shipment = $this->getMockBuilder(Shipment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getIncrementId', 'getId'])
            ->getMock();
        $shipment->expects($this->atLeastOnce())->method('getIncrementId')->willReturn($shipmentIncrementId);
        $shipment->expects($this->atLeastOnce())->method('getId')->willReturn($shipmentId);
        $shipmentCollection->expects($this->any())->method('getIterator')->willReturn(new \ArrayIterator([$shipment]));

        $track = $this->getMockBuilder(Track::class)
            ->disableOriginalConstructor()
            ->addMethods(['getNumberDetail'])
            ->onlyMethods(['setShipment'])
            ->getMock();
        $track->expects($this->atLeastOnce())->method('setShipment')->with($shipment)->willReturnSelf();
        $track->expects($this->atLeastOnce())->method('getNumberDetail')->willReturn($trackDetails);
        $trackCollection = $this->getMockBuilder(\Magento\Shipping\Model\ResourceModel\Order\Track\Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getIterator', 'setShipmentFilter'])
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
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getId', 'getProtectCode'])
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
        $shipment = $this->getMockBuilder(Shipment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEntityId', 'getProtectCode', 'getIncrementId', 'getId'])
            ->getMock();
        $shipment->expects($this->atLeastOnce())->method('getIncrementId')->willReturn($shipmentIncrementId);
        $shipment->expects($this->atLeastOnce())->method('getId')->willReturn($decodedHash['id']);
        $shipment->expects($this->atLeastOnce())->method('getEntityId')->willReturn(3);
        $shipment->expects($this->atLeastOnce())->method('getProtectCode')->willReturn($decodedHash['hash']);
        $this->shipmentRepository->expects($this->atLeastOnce())
            ->method('get')
            ->with($decodedHash['id'])
            ->willReturn($shipment);
        $track = $this->getMockBuilder(Track::class)
            ->disableOriginalConstructor()
            ->addMethods(['getNumberDetail'])
            ->onlyMethods(['setShipment'])
            ->getMock();
        $track->expects($this->atLeastOnce())->method('setShipment')->with($shipment)->willReturnSelf();
        $track->expects($this->atLeastOnce())->method('getNumberDetail')->willReturn($trackDetails);
        $trackCollection = $this->getMockBuilder(\Magento\Shipping\Model\ResourceModel\Order\Track\Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getIterator', 'setShipmentFilter'])
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
        $shipment = $this->getMockBuilder(Shipment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEntityId', 'getProtectCode'])
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

    /**
     * @dataProvider loadByHashWithTrackIdDataProvider
     * @param string $protectCodeHash
     * @param string $protectCode
     * @param string $numberDetail
     * @param array $trackDetails
     * @return void
     */
    public function testLoadByHashWithTrackId(
        string $protectCodeHash,
        string $protectCode,
        string $numberDetail,
        array $trackDetails
    ) {
        $hash = base64_encode('hash');
        $decodedHash = [
            'key' => 'track_id',
            'id' => 1,
            'hash' => $protectCodeHash,
        ];
        $this->helper->expects($this->atLeastOnce())
            ->method('decodeTrackingHash')
            ->with($hash)
            ->willReturn($decodedHash);
        $track = $this->getMockBuilder(Track::class)
            ->disableOriginalConstructor()
            ->addMethods(['getNumberDetail'])
            ->onlyMethods(['load', 'getId', 'getProtectCode'])
            ->getMock();
        $track->expects($this->atLeastOnce())->method('load')->with($decodedHash['id'])->willReturnSelf();
        $track->expects($this->atLeastOnce())->method('getId')->willReturn($decodedHash['id']);
        $track->expects($this->atLeastOnce())->method('getProtectCode')->willReturn($protectCode);
        $track->expects($this->any())->method('getNumberDetail')->willReturn($numberDetail);

        $this->trackFactory->expects($this->atLeastOnce())->method('create')->willReturn($track);
        $this->info->loadByHash($hash);

        $this->assertEquals($trackDetails, $this->info->getTrackingInfo());
    }

    /**
     * @return array
     */
    public static function loadByHashWithTrackIdDataProvider()
    {
        return [
            [
                'protectCodeHash' => 'protected_code',
                'protectCode' => 'protected_code',
                'numberDetail' => 'track_details',
                'trackDetails' => [['track_details']],
            ],
            [
                'protectCodeHash' => '0',
                'protectCode' => '0e6640',
                'numberDetail' => '',
                'trackDetails' => [],
            ],
        ];
    }
}
