<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Model\Order;

use Magento\Framework\Api\AttributeValueFactory;

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
 * @method \Magento\Sales\Api\Data\ShipmentTrackExtensionInterface getExtensionAttributes()
 */
class Track extends \Magento\Sales\Model\Order\Shipment\Track
{
    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
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
     * @return \Magento\Framework\Phrase|string
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
