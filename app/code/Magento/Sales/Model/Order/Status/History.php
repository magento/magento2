<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Status;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Model\AbstractModel;

/**
 * Order status history comments
 *
 * @api
 * @method \Magento\Sales\Model\ResourceModel\Order\Status\History _getResource()
 * @method \Magento\Sales\Model\ResourceModel\Order\Status\History getResource()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class History extends AbstractModel implements OrderStatusHistoryInterface
{
    const CUSTOMER_NOTIFICATION_NOT_APPLICABLE = 2;

    /**
     * Order instance
     *
     * @var \Magento\Sales\Model\Order
     * @since 2.0.0
     */
    protected $_order;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'sales_order_status_history';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_eventObject = 'status_history';

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
        $this->_init(\Magento\Sales\Model\ResourceModel\Order\Status\History::class);
    }

    /**
     * Set order object and grab some metadata from it
     *
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     * @since 2.0.0
     */
    public function setOrder(\Magento\Sales\Model\Order $order)
    {
        $this->_order = $order;
        $this->setStoreId($order->getStoreId());
        return $this;
    }

    /**
     * Notification flag
     *
     * @param  mixed $flag OPTIONAL (notification is not applicable by default)
     * @return $this
     * @since 2.0.0
     */
    public function setIsCustomerNotified($flag = null)
    {
        if ($flag === null) {
            $flag = self::CUSTOMER_NOTIFICATION_NOT_APPLICABLE;
        }

        return $this->setData('is_customer_notified', $flag);
    }

    /**
     * Customer Notification Applicable check method
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isCustomerNotificationNotApplicable()
    {
        return $this->getIsCustomerNotified() == self::CUSTOMER_NOTIFICATION_NOT_APPLICABLE;
    }

    /**
     * Retrieve order instance
     *
     * @codeCoverageIgnore
     *
     * @return \Magento\Sales\Model\Order
     * @since 2.0.0
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Retrieve status label
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getStatusLabel()
    {
        if ($this->getOrder()) {
            return $this->getOrder()->getConfig()->getStatusLabel($this->getStatus());
        }
        return null;
    }

    /**
     * Get store object
     *
     * @return \Magento\Store\Model\Store
     * @since 2.0.0
     */
    public function getStore()
    {
        if ($this->getOrder()) {
            return $this->getOrder()->getStore();
        }
        return $this->_storeManager->getStore();
    }

    /**
     * Set order again if required
     *
     * @return $this
     * @since 2.0.0
     */
    public function beforeSave()
    {
        parent::beforeSave();

        if (!$this->getParentId() && $this->getOrder()) {
            $this->setParentId($this->getOrder()->getId());
        }

        return $this;
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
        return $this->getData(OrderStatusHistoryInterface::COMMENT);
    }

    /**
     * Returns created_at
     *
     * @return string
     * @since 2.0.0
     */
    public function getCreatedAt()
    {
        return $this->getData(OrderStatusHistoryInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(OrderStatusHistoryInterface::CREATED_AT, $createdAt);
    }

    /**
     * Returns entity_id
     *
     * @return int
     * @since 2.0.0
     */
    public function getEntityId()
    {
        return $this->getData(OrderStatusHistoryInterface::ENTITY_ID);
    }

    /**
     * Returns entity_name
     *
     * @return string
     * @since 2.0.0
     */
    public function getEntityName()
    {
        return $this->getData(OrderStatusHistoryInterface::ENTITY_NAME);
    }

    /**
     * Returns is_customer_notified
     *
     * @return int
     * @since 2.0.0
     */
    public function getIsCustomerNotified()
    {
        return $this->getData(OrderStatusHistoryInterface::IS_CUSTOMER_NOTIFIED);
    }

    /**
     * Returns is_visible_on_front
     *
     * @return int
     * @since 2.0.0
     */
    public function getIsVisibleOnFront()
    {
        return $this->getData(OrderStatusHistoryInterface::IS_VISIBLE_ON_FRONT);
    }

    /**
     * Returns parent_id
     *
     * @return int
     * @since 2.0.0
     */
    public function getParentId()
    {
        return $this->getData(OrderStatusHistoryInterface::PARENT_ID);
    }

    /**
     * Returns status
     *
     * @return string
     * @since 2.0.0
     */
    public function getStatus()
    {
        return $this->getData(OrderStatusHistoryInterface::STATUS);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setParentId($id)
    {
        return $this->setData(OrderStatusHistoryInterface::PARENT_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setIsVisibleOnFront($isVisibleOnFront)
    {
        return $this->setData(OrderStatusHistoryInterface::IS_VISIBLE_ON_FRONT, $isVisibleOnFront);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setComment($comment)
    {
        return $this->setData(OrderStatusHistoryInterface::COMMENT, $comment);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setStatus($status)
    {
        return $this->setData(OrderStatusHistoryInterface::STATUS, $status);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setEntityName($entityName)
    {
        return $this->setData(OrderStatusHistoryInterface::ENTITY_NAME, $entityName);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Sales\Api\Data\OrderStatusHistoryExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Sales\Api\Data\OrderStatusHistoryExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\OrderStatusHistoryExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    //@codeCoverageIgnoreEnd
}
