<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Model\Order\Shipment;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Sales\Api\Data\ShipmentItemInterface;
use Magento\Sales\Model\AbstractModel;

/**
 * @method \Magento\Sales\Model\ResourceModel\Order\Shipment\Item _getResource()
 * @method \Magento\Sales\Model\ResourceModel\Order\Shipment\Item getResource()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Item extends AbstractModel implements ShipmentItemInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_shipment_item';

    /**
     * @var string
     */
    protected $_eventObject = 'shipment_item';

    /**
     * @var \Magento\Sales\Model\Order\Shipment|null
     */
    protected $_shipment = null;

    /**
     * @var \Magento\Sales\Model\Order\Item|null
     */
    protected $_orderItem = null;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $_orderItemFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItemFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_orderItemFactory = $orderItemFactory;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\ResourceModel\Order\Shipment\Item');
    }

    /**
     * Declare Shipment instance
     *
     * @codeCoverageIgnore
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return $this
     */
    public function setShipment(\Magento\Sales\Model\Order\Shipment $shipment)
    {
        $this->_shipment = $shipment;
        return $this;
    }

    /**
     * Retrieve Shipment instance
     *
     * @codeCoverageIgnore
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        return $this->_shipment;
    }

    /**
     * Declare order item instance
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return $this
     */
    public function setOrderItem(\Magento\Sales\Model\Order\Item $item)
    {
        $this->_orderItem = $item;
        $this->setOrderItemId($item->getId());
        return $this;
    }

    /**
     * Retrieve order item instance
     *
     * @return \Magento\Sales\Model\Order\Item
     */
    public function getOrderItem()
    {
        if (null === $this->_orderItem) {
            if ($this->getShipment()) {
                $this->_orderItem = $this->getShipment()->getOrder()->getItemById($this->getOrderItemId());
            } else {
                $this->_orderItem = $this->_orderItemFactory->create()->load($this->getOrderItemId());
            }
        }
        return $this->_orderItem;
    }

    /**
     * Declare qty
     *
     * @param float $qty
     * @return \Magento\Sales\Model\Order\Invoice\Item
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setQty($qty)
    {
        $this->setData('qty', $qty);
        return $this;
    }

    /**
     * Applying qty to order item
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function register()
    {
        $this->getOrderItem()->setQtyShipped($this->getOrderItem()->getQtyShipped() + $this->getQty());
        return $this;
    }

    //@codeCoverageIgnoreStart
    /**
     * Returns additional_data
     *
     * @return string
     */
    public function getAdditionalData()
    {
        return $this->getData(ShipmentItemInterface::ADDITIONAL_DATA);
    }

    /**
     * Returns description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(ShipmentItemInterface::DESCRIPTION);
    }

    /**
     * Returns name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(ShipmentItemInterface::NAME);
    }

    /**
     * Returns order_item_id
     *
     * @return int
     */
    public function getOrderItemId()
    {
        return $this->getData(ShipmentItemInterface::ORDER_ITEM_ID);
    }

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->getData(ShipmentItemInterface::PARENT_ID);
    }

    /**
     * Returns price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->getData(ShipmentItemInterface::PRICE);
    }

    /**
     * Returns product_id
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->getData(ShipmentItemInterface::PRODUCT_ID);
    }

    /**
     * Returns qty
     *
     * @return float
     */
    public function getQty()
    {
        return $this->getData(ShipmentItemInterface::QTY);
    }

    /**
     * Returns row_total
     *
     * @return float
     */
    public function getRowTotal()
    {
        return $this->getData(ShipmentItemInterface::ROW_TOTAL);
    }

    /**
     * Returns sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->getData(ShipmentItemInterface::SKU);
    }

    /**
     * Returns weight
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->getData(ShipmentItemInterface::WEIGHT);
    }

    /**
     * {@inheritdoc}
     */
    public function setParentId($id)
    {
        return $this->setData(ShipmentItemInterface::PARENT_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setRowTotal($amount)
    {
        return $this->setData(ShipmentItemInterface::ROW_TOTAL, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setPrice($price)
    {
        return $this->setData(ShipmentItemInterface::PRICE, $price);
    }

    /**
     * {@inheritdoc}
     */
    public function setWeight($weight)
    {
        return $this->setData(ShipmentItemInterface::WEIGHT, $weight);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($id)
    {
        return $this->setData(ShipmentItemInterface::PRODUCT_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderItemId($id)
    {
        return $this->setData(ShipmentItemInterface::ORDER_ITEM_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setAdditionalData($additionalData)
    {
        return $this->setData(ShipmentItemInterface::ADDITIONAL_DATA, $additionalData);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        return $this->setData(ShipmentItemInterface::DESCRIPTION, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(ShipmentItemInterface::NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function setSku($sku)
    {
        return $this->setData(ShipmentItemInterface::SKU, $sku);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Sales\Api\Data\ShipmentItemExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Sales\Api\Data\ShipmentItemExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Sales\Api\Data\ShipmentItemExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
    //@codeCoverageIgnoreEnd
}
