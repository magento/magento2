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
namespace Magento\Reports\Model;

/**
 * Events model
 *
 * @method \Magento\Reports\Model\Resource\Event _getResource()
 * @method \Magento\Reports\Model\Resource\Event getResource()
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
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Event extends \Magento\Framework\Model\AbstractModel
{
    const EVENT_PRODUCT_VIEW = 1;

    const EVENT_PRODUCT_SEND = 2;

    const EVENT_PRODUCT_COMPARE = 3;

    const EVENT_PRODUCT_TO_CART = 4;

    const EVENT_PRODUCT_TO_WISHLIST = 5;

    const EVENT_WISHLIST_SHARE = 6;

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
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Magento\Reports\Model\Event\TypeFactory $eventTypeFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
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
        $this->_init('Magento\Reports\Model\Resource\Event');
    }

    /**
     * Before Event save process
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        $date = $this->_dateFactory->create();
        $this->setLoggedAt($date->gmtDate());
        return parent::_beforeSave();
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
        if (is_null($types)) {
            $types = array();
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
