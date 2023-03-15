<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Model\Order;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\ShipmentTrackExtensionInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order\Shipment\Track as OrderShipmentTrack;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @method int getParentId()
 * @method float getWeight()
 * @method float getQty()
 * @method int getOrderId()
 * @method string getDescription()
 * @method string getTitle()
 * @method string getCarrierCode()
 * @method string getCreatedAt()
 * @method string getUpdatedAt()
 * @method ShipmentTrackExtensionInterface getExtensionAttributes()
 */
class Track extends OrderShipmentTrack
{
    /**
     * @var CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param StoreManagerInterface $storeManager
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param CarrierFactory $carrierFactory
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        StoreManagerInterface $storeManager,
        ShipmentRepositoryInterface $shipmentRepository,
        CarrierFactory $carrierFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $storeManager,
            $shipmentRepository,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_carrierFactory = $carrierFactory;
    }

    /**
     * Retrieve detail for shipment track
     *
     * @return Phrase|string
     */
    public function getNumberDetail()
    {
        $carrierInstance = $this->_carrierFactory->create($this->getCarrierCode());
        if (!$carrierInstance) {
            $custom = [];
            $custom['title'] = $this->getTitle();
            $custom['number'] = $this->getTrackNumber();
            return $custom;
        } else {
            $carrierInstance->setStore($this->getStore());
        }

        $trackingInfo = $carrierInstance->getTrackingInfo($this->getNumber());
        if (!$trackingInfo) {
            return __('No detail for number "%1"', $this->getNumber());
        }

        return $trackingInfo;
    }
}
