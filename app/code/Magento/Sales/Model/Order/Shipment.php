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
namespace Magento\Sales\Model\Order;

use Magento\Sales\Model\EntityInterface;

/**
 * Sales order shipment model
 *
 * @method \Magento\Sales\Model\Resource\Order\Shipment _getResource()
 * @method \Magento\Sales\Model\Resource\Order\Shipment getResource()
 * @method int getStoreId()
 * @method \Magento\Sales\Model\Order\Shipment setStoreId(int $value)
 * @method float getTotalWeight()
 * @method \Magento\Sales\Model\Order\Shipment setTotalWeight(float $value)
 * @method float getTotalQty()
 * @method \Magento\Sales\Model\Order\Shipment setTotalQty(float $value)
 * @method int getEmailSent()
 * @method \Magento\Sales\Model\Order\Shipment setEmailSent(int $value)
 * @method int getOrderId()
 * @method \Magento\Sales\Model\Order\Shipment setOrderId(int $value)
 * @method int getCustomerId()
 * @method \Magento\Sales\Model\Order\Shipment setCustomerId(int $value)
 * @method int getShippingAddressId()
 * @method \Magento\Sales\Model\Order\Shipment setShippingAddressId(int $value)
 * @method int getBillingAddressId()
 * @method \Magento\Sales\Model\Order\Shipment setBillingAddressId(int $value)
 * @method int getShipmentStatus()
 * @method \Magento\Sales\Model\Order\Shipment setShipmentStatus(int $value)
 * @method \Magento\Sales\Model\Order\Shipment setIncrementId(string $value)
 * @method string getCreatedAt()
 * @method \Magento\Sales\Model\Order\Shipment setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\Sales\Model\Order\Shipment setUpdatedAt(string $value)
 */
class Shipment extends \Magento\Sales\Model\AbstractModel implements EntityInterface
{
    const STATUS_NEW = 1;

    const REPORT_DATE_TYPE_ORDER_CREATED = 'order_created';

    const REPORT_DATE_TYPE_SHIPMENT_CREATED = 'shipment_created';

    /**
     * Order entity type
     *
     * @var string
     */
    protected $entityType = 'shipment';

    /**
     * Store address
     */
    const XML_PATH_STORE_ADDRESS1 = 'shipping/origin/street_line1';

    const XML_PATH_STORE_ADDRESS2 = 'shipping/origin/street_line2';

    const XML_PATH_STORE_CITY = 'shipping/origin/city';

    const XML_PATH_STORE_REGION_ID = 'shipping/origin/region_id';

    const XML_PATH_STORE_ZIP = 'shipping/origin/postcode';

    const XML_PATH_STORE_COUNTRY_ID = 'shipping/origin/country_id';

    /**
     * @var mixed
     */
    protected $_items;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Shipment\Track\Collection
     */
    protected $_tracks;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Shipment\Comment\Collection
     */
    protected $_comments;

    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_shipment';

    /**
     * @var string
     */
    protected $_eventObject = 'shipment';

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Shipment\Item\CollectionFactory
     */
    protected $_shipmentItemCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Shipment\Track\CollectionFactory
     */
    protected $_trackCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\CommentFactory
     */
    protected $_commentFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Shipment\Comment\CollectionFactory
     */
    protected $_commentCollectionFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Resource\Order\Shipment\Item\CollectionFactory $shipmentItemCollectionFactory
     * @param \Magento\Sales\Model\Resource\Order\Shipment\Track\CollectionFactory $trackCollectionFactory
     * @param Shipment\CommentFactory $commentFactory
     * @param \Magento\Sales\Model\Resource\Order\Shipment\Comment\CollectionFactory $commentCollectionFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Resource\Order\Shipment\Item\CollectionFactory $shipmentItemCollectionFactory,
        \Magento\Sales\Model\Resource\Order\Shipment\Track\CollectionFactory $trackCollectionFactory,
        \Magento\Sales\Model\Order\Shipment\CommentFactory $commentFactory,
        \Magento\Sales\Model\Resource\Order\Shipment\Comment\CollectionFactory $commentCollectionFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_shipmentItemCollectionFactory = $shipmentItemCollectionFactory;
        $this->_trackCollectionFactory = $trackCollectionFactory;
        $this->_commentFactory = $commentFactory;
        $this->_commentCollectionFactory = $commentCollectionFactory;
        parent::__construct($context, $registry, $localeDate, $dateTime, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize shipment resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Resource\Order\Shipment');
    }

    /**
     * Load shipment by increment id
     *
     * @param string $incrementId
     * @return $this
     */
    public function loadByIncrementId($incrementId)
    {
        $ids = $this->getCollection()->addAttributeToFilter('increment_id', $incrementId)->getAllIds();

        if (!empty($ids)) {
            reset($ids);
            $this->load(current($ids));
        }
        return $this;
    }

    /**
     * Declare order for shipment
     *
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function setOrder(\Magento\Sales\Model\Order $order)
    {
        $this->_order = $order;
        $this->setOrderId($order->getId())->setStoreId($order->getStoreId());
        return $this;
    }

    /**
     * Retrieve hash code of current order
     *
     * @return string
     */
    public function getProtectCode()
    {
        return (string)$this->getOrder()->getProtectCode();
    }

    /**
     * Retrieve the order the shipment for created for
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (!$this->_order instanceof \Magento\Sales\Model\Order) {
            $this->_order = $this->_orderFactory->create()->load($this->getOrderId());
        }
        return $this->_order->setHistoryEntityName($this->entityType);
    }

    /**
     * Return order history item identifier
     *
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * Retrieve billing address
     *
     * @return Address
     */
    public function getBillingAddress()
    {
        return $this->getOrder()->getBillingAddress();
    }

    /**
     * Retrieve shipping address
     *
     * @return Address
     */
    public function getShippingAddress()
    {
        return $this->getOrder()->getShippingAddress();
    }

    /**
     * Register shipment
     *
     * Apply to order, order items etc.
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function register()
    {
        if ($this->getId()) {
            throw new \Magento\Framework\Model\Exception(__('We cannot register an existing shipment'));
        }

        $totalQty = 0;
        foreach ($this->getAllItems() as $item) {
            if ($item->getQty() > 0) {
                $item->register();
                if (!$item->getOrderItem()->isDummy(true)) {
                    $totalQty += $item->getQty();
                }
            } else {
                $item->isDeleted(true);
            }
        }
        $this->setTotalQty($totalQty);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getItemsCollection()
    {
        if (empty($this->_items)) {
            $this->_items = $this->_shipmentItemCollectionFactory->create()->setShipmentFilter($this->getId());

            if ($this->getId()) {
                foreach ($this->_items as $item) {
                    $item->setShipment($this);
                }
            }
        }
        return $this->_items;
    }

    /**
     * @return array
     */
    public function getAllItems()
    {
        $items = array();
        foreach ($this->getItemsCollection() as $item) {
            if (!$item->isDeleted()) {
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * @param string|int $itemId
     * @return bool|\Magento\Sales\Model\Order\Shipment\Item
     */
    public function getItemById($itemId)
    {
        foreach ($this->getItemsCollection() as $item) {
            if ($item->getId() == $itemId) {
                return $item;
            }
        }
        return false;
    }

    /**
     * @param \Magento\Sales\Model\Order\Shipment\Item $item
     * @return $this
     */
    public function addItem(\Magento\Sales\Model\Order\Shipment\Item $item)
    {
        $item->setShipment($this)->setParentId($this->getId())->setStoreId($this->getStoreId());
        if (!$item->getId()) {
            $this->getItemsCollection()->addItem($item);
        }
        return $this;
    }

    /**
     * Retrieve tracks collection.
     *
     * @return \Magento\Sales\Model\Resource\Order\Shipment\Track\Collection
     */
    public function getTracksCollection()
    {
        if (empty($this->_tracks)) {
            $this->_tracks = $this->_trackCollectionFactory->create()->setShipmentFilter($this->getId());

            if ($this->getId()) {
                foreach ($this->_tracks as $track) {
                    $track->setShipment($this);
                }
            }
        }
        return $this->_tracks;
    }

    /**
     * @return array
     */
    public function getAllTracks()
    {
        $tracks = array();
        foreach ($this->getTracksCollection() as $track) {
            if (!$track->isDeleted()) {
                $tracks[] = $track;
            }
        }
        return $tracks;
    }

    /**
     * @param string|int $trackId
     * @return bool|\Magento\Sales\Model\Order\Shipment\Track
     */
    public function getTrackById($trackId)
    {
        foreach ($this->getTracksCollection() as $track) {
            if ($track->getId() == $trackId) {
                return $track;
            }
        }
        return false;
    }

    /**
     * @param \Magento\Sales\Model\Order\Shipment\Track $track
     * @return $this
     */
    public function addTrack(\Magento\Sales\Model\Order\Shipment\Track $track)
    {
        $track->setShipment(
            $this
        )->setParentId(
            $this->getId()
        )->setOrderId(
            $this->getOrderId()
        )->setStoreId(
            $this->getStoreId()
        );
        if (!$track->getId()) {
            $this->getTracksCollection()->addItem($track);
        }

        /**
         * Track saving is implemented in _afterSave()
         * This enforces \Magento\Framework\Model\AbstractModel::save() not to skip _afterSave()
         */
        $this->_hasDataChanges = true;

        return $this;
    }

    /**
     * Adds comment to shipment with additional possibility to send it to customer via email
     * and show it in customer account
     *
     * @param \Magento\Sales\Model\Order\Shipment\Comment $comment
     * @param bool $notify
     * @param bool $visibleOnFront
     * @return $this
     */
    public function addComment($comment, $notify = false, $visibleOnFront = false)
    {
        if (!$comment instanceof \Magento\Sales\Model\Order\Shipment\Comment) {
            $comment = $this->_commentFactory->create()->setComment(
                $comment
            )->setIsCustomerNotified(
                $notify
            )->setIsVisibleOnFront(
                $visibleOnFront
            );
        }
        $comment->setShipment($this)->setParentId($this->getId())->setStoreId($this->getStoreId());
        if (!$comment->getId()) {
            $this->getCommentsCollection()->addItem($comment);
        }
        $this->_hasDataChanges = true;
        return $this;
    }

    /**
     * Retrieve comments collection.
     *
     * @param bool $reload
     * @return \Magento\Sales\Model\Resource\Order\Shipment\Comment\Collection
     */
    public function getCommentsCollection($reload = false)
    {
        if (is_null($this->_comments) || $reload) {
            $this->_comments = $this->_commentCollectionFactory->create()->setShipmentFilter(
                $this->getId()
            )->setCreatedAtOrder();

            /**
             * When shipment created with adding comment,
             * comments collection must be loaded before we added this comment.
             */
            $this->_comments->load();

            if ($this->getId()) {
                foreach ($this->_comments as $comment) {
                    $comment->setShipment($this);
                }
            }
        }
        return $this->_comments;
    }

    /**
     * Before object save
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _beforeSave()
    {
        if ((!$this->getId() || null !== $this->_items) && !count($this->getAllItems())) {
            throw new \Magento\Framework\Model\Exception(__('We cannot create an empty shipment.'));
        }

        if (!$this->getOrderId() && $this->getOrder()) {
            $this->setOrderId($this->getOrder()->getId());
            $this->setShippingAddressId($this->getOrder()->getShippingAddress()->getId());
        }

        return parent::_beforeSave();
    }

    /**
     * @return \Magento\Framework\Model\AbstractModel
     */
    protected function _beforeDelete()
    {
        return parent::_beforeDelete();
    }

    /**
     * After object save manipulations
     *
     * @return $this
     */
    protected function _afterSave()
    {
        if (null !== $this->_items) {
            foreach ($this->_items as $item) {
                $item->save();
            }
        }

        if (null !== $this->_tracks) {
            foreach ($this->_tracks as $track) {
                $track->save();
            }
        }

        if (null !== $this->_comments) {
            foreach ($this->_comments as $comment) {
                $comment->save();
            }
        }

        return parent::_afterSave();
    }

    /**
     * Retrieve store model instance
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->getOrder()->getStore();
    }

    /**
     * Set shipping label
     *
     * @param string $label label representation (image or pdf file)
     * @return $this
     */
    public function setShippingLabel($label)
    {
        $this->setData('shipping_label', $label);
        return $this;
    }

    /**
     * Get shipping label and decode by db adapter
     *
     * @return mixed
     */
    public function getShippingLabel()
    {
        $label = $this->getData('shipping_label');
        if ($label) {
            return $this->getResource()->getReadConnection()->decodeVarbinary($label);
        }
        return $label;
    }

    /**
     * Returns increment id
     *
     * @return string
     */
    public function getIncrementId()
    {
        return $this->getData('increment_id');
    }
}
