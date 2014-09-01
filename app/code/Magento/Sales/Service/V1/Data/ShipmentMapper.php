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

namespace Magento\Sales\Service\V1\Data;

/**
 * Class ShipmentMapper
 */
class ShipmentMapper
{
    /**
     * @param ShipmentBuilder $shipmentBuilder
     * @param ShipmentItemMapper $shipmentItemMapper
     * @param ShipmentTrackMapper $shipmentTrackMapper
     */
    public function __construct(
        ShipmentBuilder $shipmentBuilder,
        ShipmentItemMapper $shipmentItemMapper,
        ShipmentTrackMapper $shipmentTrackMapper
    ) {
        $this->shipmentBuilder = $shipmentBuilder;
        $this->shipmentItemMapper = $shipmentItemMapper;
        $this->shipmentTrackMapper = $shipmentTrackMapper;
    }

    /**
     * Returns array of items
     *
     * @param \Magento\Sales\Model\Order\Shipment $object
     * @return ShipmentItem[]
     */
    protected function getItems(\Magento\Sales\Model\Order\Shipment $object)
    {
        $items = [];
        foreach ($object->getItemsCollection() as $item) {
            $items[] = $this->shipmentItemMapper->extractDto($item);
        }
        return $items;
    }

    /**
     * Returns array of tracks
     *
     * @param \Magento\Sales\Model\Order\Shipment $object
     * @return ShipmentTrack[]
     */
    protected function getTracks(\Magento\Sales\Model\Order\Shipment $object)
    {
        $items = [];
        foreach ($object->getTracksCollection() as $item) {
            $items[] = $this->shipmentTrackMapper->extractDto($item);
        }
        return $items;
    }

    /**
     * @param \Magento\Sales\Model\Order\Shipment $object
     * @return \Magento\Sales\Service\V1\Data\Shipment
     */
    public function extractDto(\Magento\Sales\Model\Order\Shipment $object)
    {
        $this->shipmentBuilder->populateWithArray($object->getData());
        $this->shipmentBuilder->setItems($this->getItems($object));
        $this->shipmentBuilder->setTracks($this->getTracks($object));
        $this->shipmentBuilder->setPackages(serialize($object->getPackages()));
        return $this->shipmentBuilder->create();
    }
}
