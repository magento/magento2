<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model;

/**
 * Events model
 *
 * @method string getLoggedAt()
 * @method \Magento\Reports\Model\Event setLoggedAt(string $value)
 * @method int getEventTypeId()
 * @method \Magento\Reports\Model\Event setEventTypeId(int $value)
 * @method int getObjectId()
 * @method \Magento\Reports\Model\Event setObjectId(int $value)
 * @method int getSubjectId()
 * @method \Magento\Reports\Model\Event setSubjectId(int $value)
 * @method int getSubtype()
 * @method \Magento\Reports\Model\Event setSubtype(int $value)
 * @method int getStoreId()
 * @method \Magento\Reports\Model\Event setStoreId(int $value)
 *
 * @api
 * @since 100.0.2
 */
class Event extends \Magento\Framework\Model\AbstractModel
{
    public const EVENT_PRODUCT_VIEW = 1;

    public const EVENT_PRODUCT_SEND = 2;

    public const EVENT_PRODUCT_COMPARE = 3;

    public const EVENT_PRODUCT_TO_CART = 4;

    public const EVENT_PRODUCT_TO_WISHLIST = 5;

    public const EVENT_WISHLIST_SHARE = 6;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $_dateFactory;

    /**
     * @var \Magento\Reports\Model\Event\TypeFactory
     */
    protected $_eventTypeFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
     * @param \Magento\Reports\Model\Event\TypeFactory $eventTypeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Magento\Reports\Model\Event\TypeFactory $eventTypeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_dateFactory = $dateFactory;
        $this->_eventTypeFactory = $eventTypeFactory;
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Reports\Model\ResourceModel\Event::class);
    }

    /**
     * Before Event save process
     *
     * @return $this
     */
    public function beforeSave()
    {
        $date = $this->_dateFactory->create();
        $this->setLoggedAt($date->gmtDate());
        return parent::beforeSave();
    }

    /**
     * Update customer type after customer login
     *
     * @param int $visitorId
     * @param int $customerId
     * @param array $types
     * @return $this
     */
    public function updateCustomerType($visitorId, $customerId, $types = null)
    {
        if ($types === null) {
            $types = [];
            $typesCollection = $this->_eventTypeFactory->create()->getCollection();
            foreach ($typesCollection as $eventType) {
                if ($eventType->getCustomerLogin()) {
                    $types[$eventType->getId()] = $eventType->getId();
                }
            }
        }
        $this->getResource()->updateCustomerType($this, $visitorId, $customerId, $types);
        return $this;
    }

    /**
     * Clean events (visitors)
     *
     * @return $this
     */
    public function clean()
    {
        $this->getResource()->clean($this);
        return $this;
    }
}
