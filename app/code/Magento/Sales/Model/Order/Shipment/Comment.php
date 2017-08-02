<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Sales\Api\Data\ShipmentCommentInterface;
use Magento\Sales\Model\AbstractModel;

/**
 * @method \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment _getResource()
 * @method \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment getResource()
 * @since 2.0.0
 */
class Comment extends AbstractModel implements ShipmentCommentInterface
{
    /**
     * Shipment instance
     *
     * @var \Magento\Sales\Model\Order\Shipment
     * @since 2.0.0
     */
    protected $_shipment;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
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
        $this->_storeManager = $storeManager;
    }

    /**
     * Initialize resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Sales\Model\ResourceModel\Order\Shipment\Comment::class);
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
     * Get store object
     *
     * @return \Magento\Store\Model\Store
     * @since 2.0.0
     */
    public function getStore()
    {
        if ($this->getShipment()) {
            return $this->getShipment()->getStore();
        }
        return $this->_storeManager->getStore();
    }

    //@codeCoverageIgnoreStart

    /**
     * Returns comment
     *
     * @return string
     * @since 2.0.0
     */
    public function getComment()
    {
        return $this->getData(ShipmentCommentInterface::COMMENT);
    }

    /**
     * Returns created_at
     *
     * @return string
     * @since 2.0.0
     */
    public function getCreatedAt()
    {
        return $this->getData(ShipmentCommentInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(ShipmentCommentInterface::CREATED_AT, $createdAt);
    }

    /**
     * Returns is_customer_notified
     *
     * @return int
     * @since 2.0.0
     */
    public function getIsCustomerNotified()
    {
        return $this->getData(ShipmentCommentInterface::IS_CUSTOMER_NOTIFIED);
    }

    /**
     * Returns is_visible_on_front
     *
     * @return int
     * @since 2.0.0
     */
    public function getIsVisibleOnFront()
    {
        return $this->getData(ShipmentCommentInterface::IS_VISIBLE_ON_FRONT);
    }

    /**
     * Returns parent_id
     *
     * @return int
     * @since 2.0.0
     */
    public function getParentId()
    {
        return $this->getData(ShipmentCommentInterface::PARENT_ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setParentId($id)
    {
        return $this->setData(ShipmentCommentInterface::PARENT_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setIsCustomerNotified($isCustomerNotified)
    {
        return $this->setData(ShipmentCommentInterface::IS_CUSTOMER_NOTIFIED, $isCustomerNotified);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setIsVisibleOnFront($isVisibleOnFront)
    {
        return $this->setData(ShipmentCommentInterface::IS_VISIBLE_ON_FRONT, $isVisibleOnFront);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setComment($comment)
    {
        return $this->setData(ShipmentCommentInterface::COMMENT, $comment);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Sales\Api\Data\ShipmentCommentExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Sales\Api\Data\ShipmentCommentExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShipmentCommentExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    //@codeCoverageIgnoreEnd
}
