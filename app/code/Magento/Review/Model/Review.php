<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Review\Model\ResourceModel\Review\Product\Collection as ProductCollection;
use Magento\Review\Model\ResourceModel\Review\Status\Collection as StatusCollection;
use Magento\Review\Api\Data\ReviewInterface;

/**
 * Review model
 *
 * @method string getCreatedAt()
 * @method \Magento\Review\Model\Review setCreatedAt($date)
 * @method \Magento\Review\Model\Review setEntityId($entityId)
 * @method int getEntityPkValue()
 * @method \Magento\Review\Model\Review setEntityPkValue($entityPkValue)
 * @method int getStatusId()
 * @method \Magento\Review\Model\Review setStatusId($statusId)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Review extends \Magento\Framework\Model\AbstractModel implements IdentityInterface, ReviewInterface
{
    /**
     * Event prefix for observer
     *
     * @var string
     */
    protected $_eventPrefix = 'review';

    /**
     * Cache tag
     */
    const CACHE_TAG = 'review_block';

    /**
     * Product entity review code
     */
    const ENTITY_PRODUCT_CODE = 'product';

    /**
     * Customer entity review code
     */
    const ENTITY_CUSTOMER_CODE = 'customer';

    /**
     * Category entity review code
     */
    const ENTITY_CATEGORY_CODE = 'category';

    /**
     * Approved review status code
     */
    const STATUS_APPROVED = 1;

    /**
     * Pending review status code
     */
    const STATUS_PENDING = 2;

    /**
     * Not Approved review status code
     */
    const STATUS_NOT_APPROVED = 3;

    /**
     * Review product collection factory
     *
     * @var \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Review status collection factory
     *
     * @var \Magento\Review\Model\ResourceModel\Review\Status\CollectionFactory
     */
    protected $_statusFactory;

    /**
     * Review model summary factory
     *
     * @var \Magento\Review\Model\Review\SummaryFactory
     */
    protected $_summaryFactory;

    /**
     * Review model summary factory
     *
     * @var \Magento\Review\Model\Review\SummaryFactory
     */
    protected $_summaryModFactory;

    /**
     * Review model summary
     *
     * @var \Magento\Review\Model\Review\Summary
     */
    protected $_reviewSummary;

    /**
     * Core model store manager interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Url interface
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlModel;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $productFactory
     * @param \Magento\Review\Model\ResourceModel\Review\Status\CollectionFactory $statusFactory
     * @param \Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory $summaryFactory
     * @param \Magento\Review\Model\Review\SummaryFactory $summaryModFactory
     * @param \Magento\Review\Model\Review\Summary $reviewSummary
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlModel
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $productFactory,
        \Magento\Review\Model\ResourceModel\Review\Status\CollectionFactory $statusFactory,
        \Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory $summaryFactory,
        \Magento\Review\Model\Review\SummaryFactory $summaryModFactory,
        \Magento\Review\Model\Review\Summary $reviewSummary,
        \Magento\Review\Model\Review\StatusFactory $reviewStatusFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlModel,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productCollectionFactory = $productFactory;
        $this->_statusFactory = $statusFactory;
        $this->_summaryFactory = $summaryFactory;
        $this->_summaryModFactory = $summaryModFactory;
        $this->_reviewSummary = $reviewSummary;
        $this->_reviewStatusFactory = $reviewStatusFactory;
        $this->_storeManager = $storeManager;
        $this->_urlModel = $urlModel;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Review\Model\ResourceModel\Review');
    }

    /**
     * Get product collection
     *
     * @return ProductCollection
     */
    public function getProductCollection()
    {
        return $this->productCollectionFactory->create();
    }

    /**
     * Get status collection
     *
     * @return StatusCollection
     */
    public function getStatusCollection()
    {
        return $this->_statusFactory->create();
    }

    /**
     * Get total reviews
     *
     * @param int $entityPkValue
     * @param bool $approvedOnly
     * @param int $storeId
     * @return int
     */
    public function getTotalReviews($entityPkValue, $approvedOnly = false, $storeId = 0)
    {
        return $this->getResource()->getTotalReviews($entityPkValue, $approvedOnly, $storeId);
    }

    /**
     * Aggregate reviews
     *
     * @return $this
     */
    public function aggregate()
    {
        $this->getResource()->aggregate($this);
        return $this;
    }

    /**
     * Get entity summary
     *
     * @param Product $product
     * @param int $storeId
     * @return void
     */
    public function getEntitySummary($product, $storeId = 0)
    {
        $summaryData = $this->_summaryModFactory->create()->setStoreId($storeId)->load($product->getId());
        $summary = new \Magento\Framework\DataObject();
        $summary->setData($summaryData->getData());
        $product->setRatingSummary($summary);
    }

    /**
     * Get pending status
     *
     * @return int
     */
    public function getPendingStatus()
    {
        return self::STATUS_PENDING;
    }

    /**
     * Get review product view url
     *
     * @return string
     */
    public function getReviewUrl()
    {
        return $this->_urlModel->getUrl('review/product/view', ['id' => $this->getReviewId()]);
    }

    /**
     * Get product view url
     *
     * @param string|int $productId
     * @param string|int $storeId
     * @return string
     */
    public function getProductUrl($productId, $storeId)
    {
        if ($storeId) {
            $this->_urlModel->setScope($storeId);
        }

        return $this->_urlModel->getUrl('catalog/product/view', ['id' => $productId]);
    }

    /**
     * Validate review summary fields
     *
     * @return bool|string[]
     */
    public function validate()
    {
        $errors = [];

        if (!\Zend_Validate::is($this->getTitle(), 'NotEmpty')) {
            $errors[] = __('Please enter a review summary.');
        }

        if (!\Zend_Validate::is($this->getNickname(), 'NotEmpty')) {
            $errors[] = __('Please enter a nickname.');
        }

        if (!\Zend_Validate::is($this->getDetail(), 'NotEmpty')) {
            $errors[] = __('Please enter a review.');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    /**
     * Perform actions after object delete
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function afterDeleteCommit()
    {
        $this->getResource()->afterDeleteCommit($this);
        return parent::afterDeleteCommit();
    }

    /**
     * Append review summary to product collection
     *
     * @param ProductCollection $collection
     * @return $this
     */
    public function appendSummary($collection)
    {
        $entityIds = [];
        foreach ($collection->getItems() as $item) {
            $entityIds[] = $item->getEntityId();
        }

        if (sizeof($entityIds) == 0) {
            return $this;
        }

        $summaryData = $this->_summaryFactory->create()
            ->addEntityFilter($entityIds)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->load();

        foreach ($collection->getItems() as $item) {
            foreach ($summaryData as $summary) {
                if ($summary->getEntityPkValue() == $item->getEntityId()) {
                    $item->setRatingSummary($summary);
                }
            }
        }

        return $this;
    }

    /**
     * Check if current review approved or not
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->getStatusId() == self::STATUS_APPROVED;
    }

    /**
     * Check if current review available on passed store
     *
     * @param int|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function isAvailableOnStore($store = null)
    {
        $store = $this->_storeManager->getStore($store);
        if ($store) {
            return in_array($store->getId(), (array) $this->getStores());
        }
        return false;
    }

    /**
     * Get review entity type id by code
     *
     * @param string $entityCode
     * @return int|bool
     */
    public function getEntityIdByCode($entityCode)
    {
        return $this->getResource()->getEntityIdByCode($entityCode);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        $tags = [];
        if ($this->getEntityPkValue()) {
            $tags[] = Product::CACHE_TAG . '_' . $this->getEntityPkValue();
        }
        return $tags;
    }

    /**
     * {@inheritdoc}
     */
    public function getReviewId()
    {
        return $this->_getData('review_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setReviewId($reviewId)
    {
        $this->setData('review_id', $reviewId);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDetailId()
    {
        return $this->_getData('detail_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setDetailId($detailId)
    {
        $this->setData('detail_id', $detailId);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->_getData('title');
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $this->setData('title', $title);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDetail()
    {
        return $this->_getData('detail');
    }

    /**
     * {@inheritdoc}
     */
    public function setDetail($detail)
    {
        $this->setData('detail', $detail);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getNickname()
    {
        return $this->_getData('nickname');
    }

    /**
     * {@inheritdoc}
     */
    public function setNickname($nickname)
    {
        $this->setData('nickname', $nickname);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->_getData('customer_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        $this->setData('customer_id', $customerId);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityCode()
    {
        return $this->_getData('entity_code');
    }

    /**
     * {@inheritdoc}
     */
    public function setEntityCode($entityCode)
    {
        $this->setData('entity_code', $entityCode);
        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
//        if(!$this->getData('status')) {
            /** @var \Magento\Review\Model\Review\Status $status */
            $status = $this->_reviewStatusFactory->create();
            $status->load($this->getStatusId());

        return $status->getStatusCode();
//            $this->setData('status', $status->getStatusCode());
//        }

        
//        return $this->getData('status');
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($statusCode)
    {
        $status = $this->_statusFactory->create()
            ->addFieldToFilter('status_code', $statusCode)
            ->getFirstItem();
        if(!$status->getId()) {
            throw new NoSuchEntityException(__("Status doesn't exist"));
        }
        $this->setStatusId($status->getId());
        
        return $this;
    }
}
