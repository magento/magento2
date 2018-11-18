<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Review\Model\Rating\OptionFactory as RatingOptionFactory;
use Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory as RatingOptionCollectionFactory;
use Magento\ReviewApi\Api\Data\RatingInterface;

/**
 * Rating model
 */
class Rating extends AbstractExtensibleModel implements IdentityInterface, RatingInterface
{
    /**
     * rating entity codes
     */
    const ENTITY_PRODUCT_CODE = 'product';
    const ENTITY_PRODUCT_REVIEW_CODE = 'product_review';
    const ENTITY_REVIEW_CODE = 'review';

    /**
     * @var RatingOptionFactory
     */
    protected $_ratingOptionFactory;

    /**
     * @var RatingOptionCollectionFactory
     */
    protected $_ratingCollectionF;

    /**
     * Rating constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param RatingOptionFactory $ratingOptionFactory
     * @param RatingOptionCollectionFactory $ratingOptionCollectionFactory
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        RatingOptionFactory $ratingOptionFactory,
        RatingOptionCollectionFactory $ratingOptionCollectionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null
    ) {
        $this->_ratingOptionFactory = $ratingOptionFactory;
        $this->_ratingCollectionF = $ratingOptionCollectionFactory;

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
        $this->_init(\Magento\Review\Model\ResourceModel\Rating::class);
    }

    /**
     * @inheritdoc
     */
    public function getRatingId(): ?int
    {
        return (int)$this->_getData(self::RATING_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRatingId(?int $ratingId): RatingInterface
    {
        $this->setData(self::RATING_ID, $ratingId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRatingEntityId(): int
    {
        return (int)$this->_getData(self::RATING_ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRatingEntityId(int $entityId): RatingInterface
    {
        $this->setData(self::RATING_ENTITY_ID, $entityId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRatingName(): string
    {
        return $this->_getData(self::RATING_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setRatingName(string $ratingName): RatingInterface
    {
        $this->setData(self::RATING_NAME, $ratingName);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRatingNames(): ?array
    {
        return $this->_getData(self::RATING_NAMES);
    }

    /**
     * @inheritdoc
     */
    public function setRatingNames(?array $ratingNames): RatingInterface
    {
        $this->setData(self::RATING_NAMES, $ratingNames);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPosition(): int
    {
        return (int)$this->_getData(self::POSITION);
    }

    /**
     * @inheritdoc
     */
    public function setPosition(int $position): RatingInterface
    {
        $this->setData(self::POSITION, $position);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isActive(): bool
    {
        return (bool)$this->_getData(self::IS_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function setIsActive($isActive): RatingInterface
    {
        $this->setData(self::IS_ACTIVE, $isActive);
        return $this;
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
    public function setStoreId($storeId): RatingInterface
    {
        $this->setData(self::STORE_ID, $storeId);
        return $this;
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
    public function setStores(array $stores): RatingInterface
    {
        $this->setData(self::STORES, $stores);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOptions()
    {
        $options = $this->getData(self::RATING_OPTIONS);
        if ($options) {
            return $options;
        } elseif ($this->getId()) {
            return $this->_ratingCollectionF->create()->addRatingFilter(
                $this->getId()
            )->setPositionOrder()->load()->getItems();
        }
        return [];
    }

    /**
     * @inheritdoc
     */
    public function setOptions(array $options)
    {
        $this->setData(self::RATING_OPTIONS, $options);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): \Magento\ReviewApi\Api\Data\RatingExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (!$extensionAttributes) {
            return $this->extensionAttributesFactory->create(RatingInterface::class);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magento\ReviewApi\Api\Data\RatingExtensionInterface $extensionAttributes
    ): RatingInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * @inheritdoc
     */
    public function getIdentities()
    {
        return [Review::CACHE_TAG];
    }

    /**
     * Add option vote
     *
     * @param int $optionId
     * @param int $entityPkValue
     * @return $this
     */
    public function addOptionVote($optionId, $entityPkValue)
    {
        $this->_ratingOptionFactory->create()->setOptionId(
            $optionId
        )->setRatingId(
            $this->getId()
        )->setReviewId(
            $this->getReviewId()
        )->setEntityPkValue(
            $entityPkValue
        )->addVote();
        return $this;
    }

    /**
     * Update option vote
     *
     * @param int $optionId
     * @return $this
     */
    public function updateOptionVote($optionId)
    {
        $this->_ratingOptionFactory->create()->setOptionId(
            $optionId
        )->setVoteId(
            $this->getVoteId()
        )->setReviewId(
            $this->getReviewId()
        )->setDoUpdate(
            1
        )->addVote();
        return $this;
    }

    /**
     * Get rating collection object
     *
     * @param int $entityPkValue
     * @param bool $onlyForCurrentStore
     * @return AbstractDb
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEntitySummary($entityPkValue, $onlyForCurrentStore = true)
    {
        $this->setEntityPkValue($entityPkValue);
        return $this->_getResource()->getEntitySummary($this, $onlyForCurrentStore);
    }

    /**
     * Get review summary
     *
     * @param int $reviewId
     * @param bool $onlyForCurrentStore
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getReviewSummary($reviewId, $onlyForCurrentStore = true)
    {
        $this->setReviewId($reviewId);
        return $this->_getResource()->getReviewSummary($this, $onlyForCurrentStore);
    }

    /**
     * Get rating entity type id by code
     *
     * @param string $entityCode
     * @return int
     */
    public function getEntityIdByCode($entityCode)
    {
        return $this->getResource()->getEntityIdByCode($entityCode);
    }
}
