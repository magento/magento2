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
namespace Magento\Sales\Model\Order\Shipment;

use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Api\Data\ShipmentCommentInterface;
use Magento\Framework\Api\AttributeDataBuilder;

/**
 * @method \Magento\Sales\Model\Resource\Order\Shipment\Comment _getResource()
 * @method \Magento\Sales\Model\Resource\Order\Shipment\Comment getResource()
 * @method \Magento\Sales\Model\Order\Shipment\Comment setParentId(int $value)
 * @method \Magento\Sales\Model\Order\Shipment\Comment setIsCustomerNotified(int $value)
 * @method \Magento\Sales\Model\Order\Shipment\Comment setIsVisibleOnFront(int $value)
 * @method \Magento\Sales\Model\Order\Shipment\Comment setComment(string $value)
 * @method \Magento\Sales\Model\Order\Shipment\Comment setCreatedAt(string $value)
 */
class Comment extends AbstractModel implements ShipmentCommentInterface
{
    /**
     * Shipment instance
     *
     * @var \Magento\Sales\Model\Order\Shipment
     */
    protected $_shipment;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param AttributeDataBuilder $customAttributeBuilder
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\StoreManagerInterface $storeManager
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
        \Magento\Framework\StoreManagerInterface $storeManager,
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
        $this->_init('Magento\Sales\Model\Resource\Order\Shipment\Comment');
    }

    /**
     * Declare Shipment instance
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
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        return $this->_shipment;
    }

    /**
     * Get store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        if ($this->getShipment()) {
            return $this->getShipment()->getStore();
        }
        return $this->_storeManager->getStore();
    }

    /**
     * Returns comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->getData(ShipmentCommentInterface::COMMENT);
    }

    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(ShipmentCommentInterface::CREATED_AT);
    }

    /**
     * Returns is_customer_notified
     *
     * @return int
     */
    public function getIsCustomerNotified()
    {
        return $this->getData(ShipmentCommentInterface::IS_CUSTOMER_NOTIFIED);
    }

    /**
     * Returns is_visible_on_front
     *
     * @return int
     */
    public function getIsVisibleOnFront()
    {
        return $this->getData(ShipmentCommentInterface::IS_VISIBLE_ON_FRONT);
    }

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->getData(ShipmentCommentInterface::PARENT_ID);
    }
}
