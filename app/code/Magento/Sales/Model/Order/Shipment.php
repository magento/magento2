<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\EntityInterface;

/**
 * Sales order shipment model
 *
 * @method \Magento\Sales\Model\ResourceModel\Order\Shipment _getResource()
 * @method \Magento\Sales\Model\ResourceModel\Order\Shipment getResource()
 * @method \Magento\Sales\Model\Order\Invoice setSendEmail(bool $value)
 * @method \Magento\Sales\Model\Order\Invoice setCustomerNote(string $value)
 * @method string getCustomerNote()
 * @method \Magento\Sales\Model\Order\Invoice setCustomerNoteNotify(bool $value)
 * @method bool getCustomerNoteNotify()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Shipment extends AbstractModel implements EntityInterface, ShipmentInterface
{
    const STATUS_NEW = 1;

    const REPORT_DATE_TYPE_ORDER_CREATED = 'order_created';

    const REPORT_DATE_TYPE_SHIPMENT_CREATED = 'shipment_created';

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
     * Order entity type
     *
     * @var string
     */
    protected $entityType = 'shipment';

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_shipment';

    /**
     * @var string
     */
    protected $_eventObject = 'shipment';

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory
     */
    protected $_shipmentItemCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory
     */
    protected $_trackCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\CommentFactory
     */
    protected $_commentFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory
     */
    protected $_commentCollectionFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory $shipmentItemCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory
     * @param Shipment\CommentFactory $commentFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory $commentCollectionFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory $shipmentItemCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory,
        \Magento\Sales\Model\Order\Shipment\CommentFactory $commentFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory $commentCollectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_shipmentItemCollectionFactory = $shipmentItemCollectionFactory;
        $this->_trackCollectionFactory = $trackCollectionFactory;
        $this->_commentFactory = $commentFactory;
        $this->_commentCollectionFactory = $commentCollectionFactory;
        $this->orderRepository = $orderRepository;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize shipment resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\ResourceModel\Order\Shipment');
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
            $this->_order = $this->orderRepository->get($this->getOrderId());
        }
        return $this->_order->setHistoryEntityName($this->entityType);
    }

    /**
     * Return order history item identifier
     *
     * @codeCoverageIgnore
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
     * Registers shipment.
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function register()
    {
        if ($this->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We cannot register an existing shipment')
            );
        }

        $totalQty = 0;

        /** @var \Magento\Sales\Model\Order\Shipment\Item $item */
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
        if (!$this->hasData(ShipmentInterface::ITEMS)) {
            $this->setItems($this->_shipmentItemCollectionFactory->create()->setShipmentFilter($this->getId()));

            if ($this->getId()) {
                foreach ($this->getItems() as $item) {
                    $item->setShipment($this);
                }
            }
        }

        return $this->getItems();
    }

    /**
     * @return array
     */
    public function getAllItems()
    {
        $items = [];
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
     * @return \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection
     */
    public function getTracksCollection()
    {
        if (!$this->hasData(ShipmentInterface::TRACKS)) {
            $this->setTracks($this->_trackCollectionFactory->create()->setShipmentFilter($this->getId()));

            if ($this->getId()) {
                foreach ($this->getTracks() as $track) {
                    $track->setShipment($this);
                }
            }
        }
        return $this->getTracks();
    }

    /**
     * @return array
     */
    public function getAllTracks()
    {
        $tracks = [];
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
     * @param \Magento\Sales\Model\Order\Shipment\Comment|string $comment
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
     * @return \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\Collection
     */
    public function getCommentsCollection($reload = false)
    {
        if (!$this->hasData(ShipmentInterface::COMMENTS) || $reload) {
            $comments = $this->_commentCollectionFactory->create()
                ->setShipmentFilter($this->getId())
                ->setCreatedAtOrder();
            $this->setComments($comments);

            if ($this->getId()) {
                foreach ($this->getComments() as $comment) {
                    $comment->setShipment($this);
                }
            }
        }
        return $this->getComments();
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
            return $this->getResource()->getConnection()->decodeVarbinary($label);
        }
        return $label;
    }

    /**
     * Returns increment id
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function getIncrementId()
    {
        return $this->getData('increment_id');
    }

    /**
     * Returns packages
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function getPackages()
    {
        return $this->getData(ShipmentInterface::PACKAGES);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function setPackages(array $packages = null)
    {
        return $this->setData(ShipmentInterface::PACKAGES, $packages);
    }

    /**
     * Returns items
     *
     * @return \Magento\Sales\Api\Data\ShipmentItemInterface[]
     */
    public function getItems()
    {
        if ($this->getData(ShipmentInterface::ITEMS) === null) {
            $collection =  $this->_shipmentItemCollectionFactory->create()->setShipmentFilter($this->getId());
            if ($this->getId()) {
                foreach ($collection as $item) {
                    $item->setShipment($this);
                }
                $this->setData(ShipmentInterface::ITEMS, $collection->getItems());
            }
        }
        return $this->getData(ShipmentInterface::ITEMS);
    }

    /**
     * Sets items
     *
     * @codeCoverageIgnore
     *
     * @param mixed $items
     * @return $this
     */
    public function setItems($items)
    {
        return $this->setData(ShipmentInterface::ITEMS, $items);
    }

    /**
     * Returns tracks
     *
     * @return \Magento\Sales\Api\Data\ShipmentTrackInterface[]
     */
    public function getTracks()
    {
        if ($this->getData(ShipmentInterface::TRACKS) === null) {
            $collection =  $this->_trackCollectionFactory->create()->setShipmentFilter($this->getId());
            if ($this->getId()) {
                foreach ($collection as $item) {
                    $item->setShipment($this);
                }
                $this->setData(ShipmentInterface::TRACKS, $collection->getItems());
            }
        }
        return $this->getData(ShipmentInterface::TRACKS);
    }

    //@codeCoverageIgnoreStart

    /**
     * Returns tracks
     *
     * @param \Magento\Sales\Api\Data\ShipmentTrackInterface[] $tracks
     * @return $this
     */
    public function setTracks($tracks)
    {
        return $this->setData(ShipmentInterface::TRACKS, $tracks);
    }

    /**
     * Returns billing_address_id
     *
     * @return int
     */
    public function getBillingAddressId()
    {
        return $this->getData(ShipmentInterface::BILLING_ADDRESS_ID);
    }

    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(ShipmentInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(ShipmentInterface::CREATED_AT, $createdAt);
    }

    /**
     * Returns customer_id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getData(ShipmentInterface::CUSTOMER_ID);
    }

    /**
     * Returns email_sent
     *
     * @return int
     */
    public function getEmailSent()
    {
        return $this->getData(ShipmentInterface::EMAIL_SENT);
    }

    /**
     * Returns order_id
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getData(ShipmentInterface::ORDER_ID);
    }

    /**
     * Returns shipment_status
     *
     * @return int
     */
    public function getShipmentStatus()
    {
        return $this->getData(ShipmentInterface::SHIPMENT_STATUS);
    }

    /**
     * Returns shipping_address_id
     *
     * @return int
     */
    public function getShippingAddressId()
    {
        return $this->getData(ShipmentInterface::SHIPPING_ADDRESS_ID);
    }

    /**
     * Returns store_id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(ShipmentInterface::STORE_ID);
    }

    /**
     * Returns total_qty
     *
     * @return float
     */
    public function getTotalQty()
    {
        return $this->getData(ShipmentInterface::TOTAL_QTY);
    }

    /**
     * Returns total_weight
     *
     * @return float
     */
    public function getTotalWeight()
    {
        return $this->getData(ShipmentInterface::TOTAL_WEIGHT);
    }

    /**
     * Returns updated_at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(ShipmentInterface::UPDATED_AT);
    }

    /**
     * Returns comments
     *
     * @return \Magento\Sales\Api\Data\ShipmentCommentInterface[]
     */
    public function getComments()
    {
        if (!$this->getId()) {
            return $this->getData(ShipmentInterface::COMMENTS);
        }

        if ($this->getData(ShipmentInterface::COMMENTS) == null) {
            $collection = $this->_commentCollectionFactory->create()
                ->setShipmentFilter($this->getId());

            foreach ($collection as $item) {
                $item->setShipment($this);
            }
            $this->setData(ShipmentInterface::COMMENTS, $collection->getItems());
        }
        return $this->getData(ShipmentInterface::COMMENTS);
    }

    /**
     * Sets comments
     *
     * @param \Magento\Sales\Api\Data\ShipmentCommentInterface[] $comments
     * @return $this
     */
    public function setComments($comments = null)
    {
        return $this->setData(ShipmentInterface::COMMENTS, $comments);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($id)
    {
        return $this->setData(ShipmentInterface::STORE_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalWeight($totalWeight)
    {
        return $this->setData(ShipmentInterface::TOTAL_WEIGHT, $totalWeight);
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalQty($qty)
    {
        return $this->setData(ShipmentInterface::TOTAL_QTY, $qty);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailSent($emailSent)
    {
        return $this->setData(ShipmentInterface::EMAIL_SENT, $emailSent);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderId($id)
    {
        return $this->setData(ShipmentInterface::ORDER_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($id)
    {
        return $this->setData(ShipmentInterface::CUSTOMER_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingAddressId($id)
    {
        return $this->setData(ShipmentInterface::SHIPPING_ADDRESS_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setBillingAddressId($id)
    {
        return $this->setData(ShipmentInterface::BILLING_ADDRESS_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setShipmentStatus($shipmentStatus)
    {
        return $this->setData(ShipmentInterface::SHIPMENT_STATUS, $shipmentStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function setIncrementId($id)
    {
        return $this->setData(ShipmentInterface::INCREMENT_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($timestamp)
    {
        return $this->setData(ShipmentInterface::UPDATED_AT, $timestamp);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Sales\Api\Data\ShipmentExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Sales\Api\Data\ShipmentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Sales\Api\Data\ShipmentExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
    //@codeCoverageIgnoreEnd
}
