<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Factory class for @see \Magento\Sales\Api\Data\ShipmentInterface
 */
class ShipmentFactory
{
    /**
     * Order converter.
     *
     * @var \Magento\Sales\Model\Convert\Order
     */
    protected $converter;

    /**
     * Shipment track factory.
     *
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    protected $trackFactory;

    /**
     * Instance name to create.
     *
     * @var string
     */
    protected $instanceName;

    /**
     * Serializer
     *
     * @var Json
     * @since 2.2.0
     */
    private $serializer;

    /**
     * Factory constructor.
     *
     * @param \Magento\Sales\Model\Convert\OrderFactory $convertOrderFactory
     * @param \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(
        \Magento\Sales\Model\Convert\OrderFactory $convertOrderFactory,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        Json $serializer = null
    ) {
        $this->converter = $convertOrderFactory->create();
        $this->trackFactory = $trackFactory;
        $this->instanceName = \Magento\Sales\Api\Data\ShipmentInterface::class;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(Json::class);
    }

    /**
     * Creates shipment instance with specified parameters.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param array $items
     * @param array|null $tracks
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     */
    public function create(\Magento\Sales\Model\Order $order, array $items = [], $tracks = null)
    {
        $shipment = $this->prepareItems($this->converter->toShipment($order), $order, $items);

        if ($tracks) {
            $shipment = $this->prepareTracks($shipment, $tracks);
        }

        return $shipment;
    }

    /**
     * Adds items to the shipment.
     *
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipment
     * @param \Magento\Sales\Model\Order $order
     * @param array $items
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function prepareItems(
        \Magento\Sales\Api\Data\ShipmentInterface $shipment,
        \Magento\Sales\Model\Order $order,
        array $items = []
    ) {
        $totalQty = 0;
        foreach ($order->getAllItems() as $orderItem) {
            if (!$this->canShipItem($orderItem, $items)) {
                continue;
            }

            /** @var \Magento\Sales\Model\Order\Shipment\Item $item */
            $item = $this->converter->itemToShipmentItem($orderItem);

            if ($orderItem->isDummy(true)) {
                $qty = 0;

                if (isset($items[$orderItem->getParentItemId()])) {
                    $productOptions = $orderItem->getProductOptions();

                    if (isset($productOptions['bundle_selection_attributes'])) {
                        $bundleSelectionAttributes = $this->serializer->unserialize(
                            $productOptions['bundle_selection_attributes']
                        );

                        if ($bundleSelectionAttributes) {
                            $qty = $bundleSelectionAttributes['qty'] * $items[$orderItem->getParentItemId()];
                            $qty = min($qty, $orderItem->getSimpleQtyToShip());

                            $item->setQty($this->castQty($orderItem, $qty));
                            $shipment->addItem($item);

                            continue;
                        } else {
                            $qty = 1;
                        }
                    }
                } else {
                    $qty = 1;
                }
            } else {
                if (isset($items[$orderItem->getId()])) {
                    $qty = min($items[$orderItem->getId()], $orderItem->getQtyToShip());
                } elseif (!count($items)) {
                    $qty = $orderItem->getQtyToShip();
                } else {
                    continue;
                }
            }

            $totalQty += $qty;

            $item->setQty($this->castQty($orderItem, $qty));
            $shipment->addItem($item);
        }
        return $shipment->setTotalQty($totalQty);
    }

    /**
     * Adds tracks to the shipment.
     *
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipment
     * @param array $tracks
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     */
    protected function prepareTracks(\Magento\Sales\Api\Data\ShipmentInterface $shipment, array $tracks)
    {
        foreach ($tracks as $data) {
            if (empty($data['number'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please enter a tracking number.')
                );
            }

            $shipment->addTrack(
                $this->trackFactory->create()->addData($data)
            );
        }

        return $shipment;
    }

    /**
     * Checks if order item can be shipped.
     *
     * Dummy item can be shipped or with his children or
     * with parent item which is included to shipment.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array $items
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function canShipItem($item, array $items = [])
    {
        if ($item->getIsVirtual() || $item->getLockedDoShip()) {
            return false;
        }

        if ($item->isDummy(true)) {
            if ($item->getHasChildren()) {
                if ($item->isShipSeparately()) {
                    return true;
                }

                foreach ($item->getChildrenItems() as $child) {
                    if ($child->getIsVirtual()) {
                        continue;
                    }

                    if (empty($items)) {
                        if ($child->getQtyToShip() > 0) {
                            return true;
                        }
                    } else {
                        if (isset($items[$child->getId()]) && $items[$child->getId()] > 0) {
                            return true;
                        }
                    }
                }

                return false;
            } elseif ($item->getParentItem()) {
                $parent = $item->getParentItem();

                if (empty($items)) {
                    return $parent->getQtyToShip() > 0;
                } else {
                    return isset($items[$parent->getId()]) && $items[$parent->getId()] > 0;
                }
            }
        } else {
            return $item->getQtyToShip() > 0;
        }
    }

    /**
     * @param Item $item
     * @param string|int|float $qty
     * @return float|int
     * @since 2.1.2
     */
    private function castQty(\Magento\Sales\Model\Order\Item $item, $qty)
    {
        if ($item->getIsQtyDecimal()) {
            $qty = (double)$qty;
        } else {
            $qty = (int)$qty;
        }

        return $qty > 0 ? $qty : 0;
    }
}
