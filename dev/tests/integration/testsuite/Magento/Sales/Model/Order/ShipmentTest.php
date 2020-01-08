<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Payment\Helper\Data;
use Magento\Sales\Api\Data\CommentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/Sales/_files/order.php
 */
class ShipmentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->shipmentRepository = $this->objectManager->get(ShipmentRepositoryInterface::class);
    }

    /**
     * Check the correctness and stability of set/get packages of shipment
     *
     * @magentoAppArea frontend
     */
    public function testPackages()
    {
        $order = $this->getOrder('100000001');

        $payment = $order->getPayment();
        $paymentInfoBlock = $this->objectManager->get(Data::class)
            ->getInfoBlock($payment);
        $payment->setBlockMock($paymentInfoBlock);

        $items = [];
        foreach ($order->getItems() as $item) {
            $items[$item->getId()] = $item->getQtyOrdered();
        }
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->objectManager->get(ShipmentFactory::class)->create($order, $items);

        $packages = [['1'], ['2']];

        $shipment->setPackages($packages);
        $saved = $this->shipmentRepository->save($shipment);
        self::assertEquals($packages, $saved->getPackages());
    }

    /**
     * Check that getTracksCollection() always return collection instance.
     */
    public function testAddTrack()
    {
        $order = $this->getOrder('100000001');

        /** @var ShipmentTrackInterface $track */
        $track = $this->objectManager->create(ShipmentTrackInterface::class);
        $track->setNumber('Test Number')
            ->setTitle('Test Title')
            ->setCarrierCode('Test CODE');

        $items = [];
        foreach ($order->getItems() as $item) {
            $items[$item->getId()] = $item->getQtyOrdered();
        }
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->objectManager->get(ShipmentFactory::class)
            ->create($order, $items);
        $shipment->addTrack($track);
        $this->shipmentRepository->save($shipment);
        $saved = $this->shipmentRepository->get((int)$shipment->getEntityId());
        self::assertNotEmpty($saved->getTracks());
    }

    /**
     * Checks adding comment to the shipment entity.
     */
    public function testAddComment()
    {
        $message1 = 'Test Comment 1';
        $message2 = 'Test Comment 2';
        $order = $this->getOrder('100000001');

        /** @var ShipmentInterface $shipment */
        $shipment = $this->objectManager->create(ShipmentInterface::class);
        $shipment->setOrder($order)
            ->addItem($this->objectManager->create(ShipmentItemInterface::class))
            ->addComment($message1)
            ->addComment($message2);

        $saved = $this->shipmentRepository->save($shipment);

        $comments = $saved->getComments();
        $actual = array_map(
            function (CommentInterface $comment) {
                return $comment->getComment();
            },
            $comments
        );
        self::assertEquals(2, count($actual));
        self::assertEquals([$message1, $message2], $actual);
    }

    /**
     * Gets order entity by increment id.
     *
     * @param string $incrementId
     * @return OrderInterface
     */
    private function getOrder(string $incrementId): OrderInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('increment_id', $incrementId)
            ->create();

        /** @var OrderRepositoryInterface $repository */
        $repository = $this->objectManager->get(OrderRepositoryInterface::class);
        $items = $repository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }

    /**
     * Check that getTracksCollection() returns only order related tracks.
     *
     * @magentoDataFixture Magento/Sales/_files/two_orders_with_order_items.php
     */
    public function testGetTracksCollection()
    {
        $order = $this->getOrder('100000001');
        $items = [];
        foreach ($order->getItems() as $item) {
            $items[$item->getId()] = $item->getQtyOrdered();
        }
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->objectManager->get(ShipmentFactory::class)
            ->create($order, $items);

        $tracks = $shipment->getTracksCollection();
        self::assertTrue(empty($tracks->getItems()));

        /** @var ShipmentTrackInterface $track */
        $track = $this->objectManager->create(ShipmentTrackInterface::class);
        $track->setNumber('Test Number')
            ->setTitle('Test Title')
            ->setCarrierCode('Test CODE');

        $shipment->addTrack($track);
        $this->shipmentRepository->save($shipment);
        $shipmentTracksCollection = $shipment->getTracksCollection();

        $secondOrder = $this->getOrder('100000002');
        $secondOrderItems = [];
        foreach ($secondOrder->getItems() as $item) {
            $secondOrderItems[$item->getId()] = $item->getQtyOrdered();
        }
        /** @var \Magento\Sales\Model\Order\Shipment $secondOrderShipment */
        $secondOrderShipment = $this->objectManager->get(ShipmentFactory::class)
            ->create($secondOrder, $secondOrderItems);

        /** @var ShipmentTrackInterface $secondShipmentTrack */
        $secondShipmentTrack = $this->objectManager->create(ShipmentTrackInterface::class);
        $secondShipmentTrack->setNumber('Test Number2')
            ->setTitle('Test Title2')
            ->setCarrierCode('Test CODE2');

        $secondOrderShipment->addTrack($secondShipmentTrack);
        $this->shipmentRepository->save($secondOrderShipment);
        $secondShipmentTrackCollection = $secondOrderShipment->getTracksCollection();

        self::assertEquals($shipmentTracksCollection->getColumnValues('id'), [$track->getEntityId()]);
        self::assertEquals(
            $secondShipmentTrackCollection->getColumnValues('id'),
            [$secondShipmentTrack->getEntityId()]
        );
    }
}
