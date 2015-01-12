<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Status;

use Magento\Framework\Api\AttributeDataBuilder;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Model\AbstractModel;

/**
 * Order status history comments
 *
 * @method \Magento\Sales\Model\Resource\Order\Status\History _getResource()
 * @method \Magento\Sales\Model\Resource\Order\Status\History getResource()
 * @method \Magento\Sales\Model\Order\Status\History setParentId(int $value)
 * @method \Magento\Sales\Model\Order\Status\History setIsVisibleOnFront(int $value)
 * @method \Magento\Sales\Model\Order\Status\History setComment(string $value)
 * @method \Magento\Sales\Model\Order\Status\History setStatus(string $value)
 * @method \Magento\Sales\Model\Order\Status\History setCreatedAt(string $value)
 */
class History extends AbstractModel implements OrderStatusHistoryInterface
{
    const CUSTOMER_NOTIFICATION_NOT_APPLICABLE = 2;

    /**
     * Order instance
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_status_history';

    /**
     * @var string
     */
    protected $_eventObject = 'status_history';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param AttributeDataBuilder $customAttributeBuilder
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\MetadataServiceInterface $metadataService,
        AttributeDataBuilder $customAttributeBuilder,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $metadataService,
            $customAttributeBuilder,
            $localeDate,
            $dateTime,
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
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Resource\Order\Status\History');
    }

    /**
     * Set order object and grab some metadata from it
     *
     * @param \Magento\Sales\Model\Order $order
     * @return $this
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
     */
    public function setIsCustomerNotified($flag = null)
    {
        if (is_null($flag)) {
            $flag = self::CUSTOMER_NOTIFICATION_NOT_APPLICABLE;
        }

        return $this->setData('is_customer_notified', $flag);
    }

    /**
     * Customer Notification Applicable check method
     *
     * @return boolean
     */
    public function isCustomerNotificationNotApplicable()
    {
        return $this->getIsCustomerNotified() == self::CUSTOMER_NOTIFICATION_NOT_APPLICABLE;
    }

    /**
     * Retrieve order instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Retrieve status label
     *
     * @return string|null
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
     */
    public function beforeSave()
    {
        parent::beforeSave();

        if (!$this->getParentId() && $this->getOrder()) {
            $this->setParentId($this->getOrder()->getId());
        }

        return $this;
    }

    /**
     * Returns comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->getData(OrderStatusHistoryInterface::COMMENT);
    }

    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(OrderStatusHistoryInterface::CREATED_AT);
    }

    /**
     * Returns entity_id
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(OrderStatusHistoryInterface::ENTITY_ID);
    }

    /**
     * Returns entity_name
     *
     * @return string
     */
    public function getEntityName()
    {
        return $this->getData(OrderStatusHistoryInterface::ENTITY_NAME);
    }

    /**
     * Returns is_customer_notified
     *
     * @return int
     */
    public function getIsCustomerNotified()
    {
        return $this->getData(OrderStatusHistoryInterface::IS_CUSTOMER_NOTIFIED);
    }

    /**
     * Returns is_visible_on_front
     *
     * @return int
     */
    public function getIsVisibleOnFront()
    {
        return $this->getData(OrderStatusHistoryInterface::IS_VISIBLE_ON_FRONT);
    }

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->getData(OrderStatusHistoryInterface::PARENT_ID);
    }

    /**
     * Returns status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(OrderStatusHistoryInterface::STATUS);
    }
}
