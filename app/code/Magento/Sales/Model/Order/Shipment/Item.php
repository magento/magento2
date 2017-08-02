<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Model\Order\Shipment;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Sales\Api\Data\ShipmentItemInterface;
use Magento\Sales\Model\AbstractModel;

/**
 * @api
 * @method \Magento\Sales\Model\ResourceModel\Order\Shipment\Item _getResource()
 * @method \Magento\Sales\Model\ResourceModel\Order\Shipment\Item getResource()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Item extends AbstractModel implements ShipmentItemInterface
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'sales_shipment_item';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_eventObject = 'shipment_item';

    /**
     * @var \Magento\Sales\Model\Order\Shipment|null
     * @since 2.0.0
     */
    protected $_shipment = null;

    /**
     * @var \Magento\Sales\Model\Order\Item|null
     * @since 2.0.0
     */
    protected $_orderItem = null;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Sales\Model\ResourceModel\Order\Shipment\Item::class);
    }

    /**
     * Declare Shipment instance
     *
     * @codeCoverageIgnore
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getAdditionalData()
    {
        return $this->getData(ShipmentItemInterface::ADDITIONAL_DATA);
    }

    /**
     * Returns description
     *
     * @return string
     * @since 2.0.0
     */
    public function getDescription()
    {
        return $this->getData(ShipmentItemInterface::DESCRIPTION);
    }

    /**
     * Returns name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->getData(ShipmentItemInterface::NAME);
    }

    /**
     * Returns order_item_id
     *
     * @return int
     * @since 2.0.0
     */
    public function getOrderItemId()
    {
        return $this->getData(ShipmentItemInterface::ORDER_ITEM_ID);
    }

    /**
     * Returns parent_id
     *
     * @return int
     * @since 2.0.0
     */
    public function getParentId()
    {
        return $this->getData(ShipmentItemInterface::PARENT_ID);
    }

    /**
     * Returns price
     *
     * @return float
     * @since 2.0.0
     */
    public function getPrice()
    {
        return $this->getData(ShipmentItemInterface::PRICE);
    }

    /**
     * Returns product_id
     *
     * @return int
     * @since 2.0.0
     */
    public function getProductId()
    {
        return $this->getData(ShipmentItemInterface::PRODUCT_ID);
    }

    /**
     * Returns qty
     *
     * @return float
     * @since 2.0.0
     */
    public function getQty()
    {
        return $this->getData(ShipmentItemInterface::QTY);
    }

    /**
     * Returns row_total
     *
     * @return float
     * @since 2.0.0
     */
    public function getRowTotal()
    {
        return $this->getData(ShipmentItemInterface::ROW_TOTAL);
    }

    /**
     * Returns sku
     *
     * @return string
     * @since 2.0.0
     */
    public function getSku()
    {
        return $this->getData(ShipmentItemInterface::SKU);
    }

    /**
     * Returns weight
     *
     * @return float
     * @since 2.0.0
     */
    public function getWeight()
    {
        return $this->getData(ShipmentItemInterface::WEIGHT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setParentId($id)
    {
        return $this->setData(ShipmentItemInterface::PARENT_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setRowTotal($amount)
    {
        return $this->setData(ShipmentItemInterface::ROW_TOTAL, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setPrice($price)
    {
        return $this->setData(ShipmentItemInterface::PRICE, $price);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setWeight($weight)
    {
        return $this->setData(ShipmentItemInterface::WEIGHT, $weight);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setProductId($id)
    {
        return $this->setData(ShipmentItemInterface::PRODUCT_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setOrderItemId($id)
    {
        return $this->setData(ShipmentItemInterface::ORDER_ITEM_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setAdditionalData($additionalData)
    {
        return $this->setData(ShipmentItemInterface::ADDITIONAL_DATA, $additionalData);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setDescription($description)
    {
        return $this->setData(ShipmentItemInterface::DESCRIPTION, $description);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setName($name)
    {
        return $this->setData(ShipmentItemInterface::NAME, $name);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setSku($sku)
    {
        return $this->setData(ShipmentItemInterface::SKU, $sku);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Sales\Api\Data\ShipmentItemExtensionInterface|null
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Sales\Api\Data\ShipmentItemExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    //@codeCoverageIgnoreEnd
}
