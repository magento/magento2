<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Review\Model\ResourceModel\Review\Status\Collection as StatusCollection;
use Magento\Review\Model\ResourceModel\Review\Status\CollectionFactory as StatusCollectionFactory;
use Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory as SummaryCollectionFactory;
use Magento\Review\Model\Review\Summary;
use Magento\Review\Model\Review\SummaryFactory;
use Magento\ReviewApi\Api\Data\ReviewExtensionInterface;
use Magento\ReviewApi\Api\Data\ReviewInterface;
use Magento\ReviewApi\Model\AggregatorInterface;
use Magento\ReviewApi\Model\ReviewValidatorInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Review model
 */
class Review extends AbstractExtensibleModel implements IdentityInterface, ReviewInterface
{
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
     * Event prefix for observer
     *
     * @var string
     */
    protected $_eventPrefix = 'review';

    /**
     * @var
     */
    protected $ratings;

    /**
     * Review product collection factory
     *
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Review status collection factory
     *
     * @var StatusCollectionFactory
     */
    protected $_statusFactory;

    /**
     * Review summary collection factory
     *
     * @var SummaryCollectionFactory
     */
    protected $_summaryFactory;

    /**
     * Review model summary factory
     *
     * @var SummaryFactory
     */
    protected $_summaryModFactory;

    /**
     * Review model summary
     *
     * @var Summary
     */
    protected $_reviewSummary;

    /**
     * Core model store manager interface
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Url interface
     *
     * @var UrlInterface
     */
    protected $_urlModel;

    /**
     * @var AggregatorInterface
     */
    private $reviewAggregator;

    /**
     * @var ReviewValidatorInterface
     */
    private $reviewValidator;

    /**
     * Review constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param ProductCollectionFactory $productFactory
     * @param StatusCollectionFactory $statusFactory
     * @param SummaryCollectionFactory $summaryFactory
     * @param SummaryFactory $summaryModFactory
     * @param Summary $reviewSummary
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlModel
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param AggregatorInterface|null $reviewAggregator
     * @param ReviewValidatorInterface|null $reviewValidator
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductCollectionFactory $productFactory,
        StatusCollectionFactory $statusFactory,
        SummaryCollectionFactory $summaryFactory,
        SummaryFactory $summaryModFactory,
        Summary $reviewSummary,
        StoreManagerInterface $storeManager,
        UrlInterface $urlModel,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,
        AggregatorInterface $reviewAggregator = null,
        ReviewValidatorInterface $reviewValidator = null
    ) {
        $this->productCollectionFactory = $productFactory;
        $this->_statusFactory = $statusFactory;
        $this->_summaryFactory = $summaryFactory;
        $this->_summaryModFactory = $summaryModFactory;
        $this->_reviewSummary = $reviewSummary;
        $this->_storeManager = $storeManager;
        $this->_urlModel = $urlModel;
        $this->reviewAggregator = $reviewAggregator
            ?: ObjectManager::getInstance()->get(AggregatorInterface::class);
        $this->reviewValidator = $reviewValidator
            ?: ObjectManager::getInstance()->get(ReviewValidatorInterface::class);

        $extensionFactory = $extensionFactory
            ?: ObjectManager::getInstance()->get(ExtensionAttributesFactory::class);
        $customAttributeFactory = $customAttributeFactory
            ?: ObjectManager::getInstance()->get(AttributeValueFactory::class);

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
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Magento\Review\Model\ResourceModel\Review::class);
    }

    /**
     * @inheritdoc
     */
    public function afterDeleteCommit()
    {
        $this->getResource()->afterDeleteCommit($this);
        return parent::afterDeleteCommit();
    }

    /**
     * @inheritdoc
     */
    public function getReviewId()
    {
        return $this->_getData(self::REVIEW_ID);
    }

    /**
     * @inheritdoc
     */
    public function setReviewId($reviewId): ReviewInterface
    {
        $this->setData(self::REVIEW_ID, $reviewId);
        return$this;
    }

    /**
     * @inheritdoc
     */
    public function getStoreId(): ?int
    {
        return $this->_getData(self::STORE_ID) ? (int)$this->_getData(self::STORE_ID) : null;
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId): ReviewInterface
    {
        $this->setData(self::STORE_ID, $storeId);
        return$this;
    }

    /**
     * @inheritdoc
     */
    public function getStores(): array
    {
        return $this->_getData(self::STORES) ?: [];
    }

    /**
     * @inheritdoc
     */
    public function setStores(array $stores): ReviewInterface
    {
        $this->setData(self::STORES, $stores);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return $this->_getData(self::TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setTitle(string $title): ReviewInterface
    {
        $this->setData(self::TITLE, $title);
        return$this;
    }

    /**
     * @inheritdoc
     */
    public function getReviewText(): string
    {
        return $this->_getData(self::REVIEW_TEXT);
    }

    /**
     * @inheritdoc
     */
    public function setReviewText(string $text): ReviewInterface
    {
        $this->setData(self::REVIEW_TEXT, $text);
        return$this;
    }

    /**
     * Set review detail
     *
     * @param string $text
     * @deprecated
     * @see \Magento\Review\Model\Review::setReviewText
     * @return $this
     */
    public function setDetail($text)
    {
        $this->setReviewText($text);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerNickname(): string
    {
        return $this->_getData(self::CUSTOMER_NICKNAME);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerNickname(string $nickname): ReviewInterface
    {
        $this->setData(self::CUSTOMER_NICKNAME, $nickname);
        return$this;
    }

    /**
     * Set customer nickname
     *
     * @param string $nickname
     * @deprecated
     * @see \Magento\Review\Model\Review::setCustomerNickname
     * @return $this
     */
    public function setNickname($nickname)
    {
        $this->setCustomerNickname($nickname);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId(): ?int
    {
        return $this->_getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId(?int $customerId): ReviewInterface
    {
        $this->setData(self::CUSTOMER_ID, $customerId);
        return$this;
    }

    /**
     * @inheritdoc
     */
    public function getReviewEntityId(): int
    {
        return (int)$this->_getData(self::REVIEW_ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setReviewEntityId(int $entityId): ReviewInterface
    {
        $this->setData(self::REVIEW_ENTITY_ID, $entityId);
        return$this;
    }

    /**
     * Set entity pk value
     *
     * @deprecated
     * @see \Magento\Review\Model\Review::setReviewEntityId
     * @param int $entityType
     * @return $this
     */
    public function setEntityType($entityType)
    {
        $this->setReviewEntityId((int)$entityType);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRelatedEntityId(): int
    {
        return (int)$this->_getData(self::RELATED_ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRelatedEntityId(int $entityId): ReviewInterface
    {
        $this->setData(self::RELATED_ENTITY_ID, $entityId);
        return$this;
    }

    /**
     * Set entity pk value
     *
     * @deprecated
     * @see \Magento\Review\Model\Review::setRelatedEntityId
     * @param int $entityPkValue
     * @return $this
     */
    public function setEntityPkValue($entityPkValue)
    {
        $this->setRelatedEntityId((int)$entityPkValue);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus(): int
    {
        return (int)$this->_getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus(int $status): ReviewInterface
    {
        $this->setData(self::STATUS, $status);
        return$this;
    }

    /**
     * Set status id
     *
     * @deprecated
     * @see \Magento\Review\Model\Review::setStatus
     * @param int $statusId
     * @return $this
     */
    public function setStatusId($statusId)
    {
        $this->setStatus((int)$statusId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->_getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(?string $createdAt): ReviewInterface
    {
        $this->setData(self::CREATED_AT, $createdAt);
        return$this;
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->_getData(self::UPDATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(?string $updatedAt): ReviewInterface
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
        return$this;
    }

    /**
     * @inheritdoc
     */
    public function getRatings(): array
    {
        return $this->_getData(self::RATINGS) ?: [];
    }

    /**
     * Set rating votes
     *
     * @deprecated
     * @see \Magento\Review\Model\Review::getRatings
     * @return array
     */
    public function getRatingVotes()
    {
        return $this->getRatings();
    }

    /**
     * @inheritdoc
     */
    public function setRatings(array $ratings): ReviewInterface
    {
        $this->setData(self::RATINGS, $ratings);
        return$this;
    }

    /**
     * Set rating votes
     *
     * @param \Magento\Review\Model\ResourceModel\Rating\Option\Vote\Collection|array $ratingVotes
     * @deprecated
     * @see \Magento\Review\Model\Review::setRatings
     * @return ReviewInterface
     */
    public function setRatingVotes($ratingVotes)
    {
        return $this->setRatings($ratingVotes->getItems());
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ReviewExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (!$extensionAttributes) {
            return $this->extensionAttributesFactory->create(ReviewInterface::class);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        ReviewExtensionInterface $extensionAttributes
    ): ReviewInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        $tags = [];
        if ($this->getRelatedEntityId()) {
            $tags[] = \Magento\Catalog\Model\Product::CACHE_TAG . '_' . $this->getRelatedEntityId();
        }
        return $tags;
    }

    /**
     * Validate review data
     *
     * @return bool|string[]
     */
    public function validate()
    {
        $validationResult = $this->reviewValidator->validate($this);
        if ($validationResult->isValid()) {
            return true;
        }
        return $validationResult->getErrors();
    }

    /**
     * Aggregate reviews
     *
     * @return $this
     */
    public function aggregate()
    {
        $this->reviewAggregator->aggregate($this);
        return $this;
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
     * Check if current review approved or not
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->getStatus() == self::STATUS_APPROVED;
    }

    /**
     * Check if current review is pending approval
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->getStatus() == self::STATUS_PENDING;
    }

    /**
     * Check if current review is rejected
     *
     * @return bool
     */
    public function isRejected()
    {
        return $this->getStatus() == self::STATUS_NOT_APPROVED;
    }

    /**
     * Check if current review available on passed store
     *
     * @param int|\Magento\Store\Model\Store $store
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
     * Get total reviews
     *
     * @param int $relatedEntityId
     * @param bool $approvedOnly
     * @param int $storeId
     * @return int
     */
    public function getTotalReviews($relatedEntityId, $approvedOnly = false, $storeId = 0)
    {
        return $this->getResource()->getTotalReviews($relatedEntityId, $approvedOnly, $storeId);
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
     * Get product collection
     *
     * @return \Magento\Review\Model\ResourceModel\Review\Product\Collection
     */
    public function getProductCollection()
    {
        return $this->productCollectionFactory->create();
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
     * Get review product view url
     *
     * @return string
     */
    public function getReviewUrl()
    {
        return $this->_urlModel->getUrl('review/product/view', ['id' => $this->getReviewId()]);
    }

    /**
     * Get entity summary
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $storeId
     * @return void
     */
    public function getEntitySummary($product, $storeId = 0)
    {
        $summaryData = $this->_summaryModFactory->create()->setStoreId((int)$storeId)->load($product->getId());
        $summary = new \Magento\Framework\DataObject();
        $summary->setData($summaryData->getData());
        $product->setRatingSummary($summary);
    }

    /**
     * Append review summary to product collection
     *
     * @param \Magento\Review\Model\ResourceModel\Review\Product\Collection $collection
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
            if (!$item->getRatingSummary()) {
                $item->setRatingSummary(new DataObject());
            }
        }

        return $this;
    }
}
