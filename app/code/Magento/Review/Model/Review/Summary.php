<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Model\Review;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Review\Model\ResourceModel\Review\Summary as ReviewSummaryResource;
use Magento\Review\Model\ResourceModel\Review\Summary\Collection as ReviewSummaryCollection;
use Magento\ReviewApi\Api\Data\ReviewSummaryInterface;

/**
 * Review summary
 */
class Summary extends AbstractExtensibleModel implements ReviewSummaryInterface
{
    /**
     * Summary constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param ReviewSummaryResource $resource
     * @param ReviewSummaryCollection $resourceCollection
     * @param array $data
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ReviewSummaryResource $resource,
        ReviewSummaryCollection $resourceCollection,
        array $data = [],
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null
    ) {
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
        $this->_init(\Magento\Review\Model\ResourceModel\Review\Summary::class);
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryId(): ?int
    {
        return $this->_getData(self::PRIMARY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setPrimaryId(?int $primaryId): ReviewSummaryInterface
    {
        $this->setData(self::PRIMARY_ID, $primaryId);
        return$this;
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
    public function setRelatedEntityId(int $entityId): ReviewSummaryInterface
    {
        $this->setData(self::RELATED_ENTITY_ID, $entityId);
        return$this;
    }

    /**
     * @inheritdoc
     */
    public function getRelatedEntityTypeId(): int
    {
        return (int)$this->_getData(self::RELATED_ENTITY_TYPE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRelatedEntityTypeId(int $entityTypeId): ReviewSummaryInterface
    {
        $this->setData(self::RELATED_ENTITY_TYPE_ID, $entityTypeId);
        return$this;
    }

    /**
     * @inheritdoc
     */
    public function getReviewsCount()
    {
        return (int)$this->_getData(self::REVIEWS_COUNT);
    }

    /**
     * @inheritdoc
     */
    public function setReviewsCount($count)
    {
        $this->setData(self::REVIEWS_COUNT, $count);
        return$this;
    }

    /**
     * @inheritdoc
     */
    public function getRatingSummary()
    {
        return (int)$this->_getData(self::RATING_SUMMARY);
    }

    /**
     * @inheritdoc
     */
    public function setRatingSummary($summary)
    {
        $this->setData(self::RATING_SUMMARY, $summary);
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
    public function setStoreId($storeId): ReviewSummaryInterface
    {
        $this->setData(self::STORE_ID, $storeId);
        return$this;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): \Magento\ReviewApi\Api\Data\ReviewSummaryExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (!$extensionAttributes) {
            return $this->extensionAttributesFactory->create(ReviewSummaryInterface::class);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magento\ReviewApi\Api\Data\ReviewSummaryExtensionInterface $extensionAttributes
    ): ReviewSummaryInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get entity primary key value
     *
     * @deprecated
     * @return int
     */
    public function getEntityPkValue()
    {
        return $this->getRelatedEntityId();
    }
}
