<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterface;
use Magento\Sales\Api\Data\ShipmentPackageCreationInterface;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterface;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentCommentCreationInterface;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\ShipmentDocumentFactory\ExtensionAttributesProcessor;

/**
 * Class ShipmentDocumentFactory
 *
 * @api
 * @since 100.1.2
 */
class ShipmentDocumentFactory
{
    /**
     * @var ShipmentFactory
     */
    private $shipmentFactory;

    /**
     * @var TrackFactory
     */
    private $trackFactory;

    /**
     * @var HydratorPool
     */
    private $hydratorPool;

    /**
     * @var ExtensionAttributesProcessor
     */
    private $extensionAttributesProcessor;

    /**
     * ShipmentDocumentFactory constructor.
     *
     * @param ShipmentFactory $shipmentFactory
     * @param HydratorPool $hydratorPool
     * @param TrackFactory $trackFactory
     * @param ExtensionAttributesProcessor $extensionAttributesProcessor
     */
    public function __construct(
        ShipmentFactory $shipmentFactory,
        HydratorPool $hydratorPool,
        TrackFactory $trackFactory,
        ?ExtensionAttributesProcessor $extensionAttributesProcessor = null
    ) {
        $this->shipmentFactory = $shipmentFactory;
        $this->trackFactory = $trackFactory;
        $this->hydratorPool = $hydratorPool;
        $this->extensionAttributesProcessor = $extensionAttributesProcessor ?: ObjectManager::getInstance()
            ->get(ExtensionAttributesProcessor::class);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param OrderInterface $order
     * @param ShipmentItemCreationInterface[] $items
     * @param ShipmentTrackCreationInterface[] $tracks
     * @param ShipmentCommentCreationInterface|null $comment
     * @param bool $appendComment
     * @param ShipmentPackageCreationInterface[] $packages
     * @param ShipmentCreationArgumentsInterface|null $arguments
     * @return ShipmentInterface
     * @since 100.1.2
     */
    public function create(
        OrderInterface $order,
        array $items = [],
        array $tracks = [],
        ?ShipmentCommentCreationInterface $comment = null,
        $appendComment = false,
        array $packages = [],
        ?ShipmentCreationArgumentsInterface $arguments = null
    ) {
        $shipmentItems = empty($items)
            ? $this->getQuantitiesFromOrderItems($order->getItems())
            : $this->getQuantitiesFromShipmentItems($items);

        /** @var Shipment $shipment */
        $shipment = $this->shipmentFactory->create(
            $order,
            $shipmentItems
        );

        if (null !== $arguments) {
            $this->extensionAttributesProcessor->execute($shipment, $arguments);
        }

        foreach ($tracks as $track) {
            $hydrator = $this->hydratorPool->getHydrator(
                \Magento\Sales\Api\Data\ShipmentTrackCreationInterface::class
            );
            $shipment->addTrack($this->trackFactory->create(['data' => $hydrator->extract($track)]));
        }

        if ($comment) {
            $shipment->addComment(
                $comment->getComment(),
                $appendComment,
                $comment->getIsVisibleOnFront()
            );

            if ($appendComment) {
                $shipment->setCustomerNote($comment->getComment());
                $shipment->setCustomerNoteNotify($appendComment);
            }
        }

        return $shipment;
    }

    /**
     * Translate OrderItemInterface array to product id => product quantity array.
     *
     * @param OrderItemInterface[] $items
     * @return int[]
     */
    private function getQuantitiesFromOrderItems(array $items)
    {
        $shipmentItems = [];
        foreach ($items as $item) {
            if (!$item->getIsVirtual() && (!$item->getParentItem() || $item->isShipSeparately())) {
                $shipmentItems[$item->getItemId()] = $item->getQtyOrdered();
            }
        }
        return $shipmentItems;
    }

    /**
     * Translate ShipmentItemCreationInterface array to product id => product quantity array.
     *
     * @param ShipmentItemCreationInterface[] $items
     * @return int[]
     */
    private function getQuantitiesFromShipmentItems(array $items)
    {
        $shipmentItems = [];
        foreach ($items as $item) {
            $shipmentItems[$item->getOrderItemId()] = $item->getQty();
        }
        return $shipmentItems;
    }
}
