<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface ReviewSummaryInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const PRIMARY_ID = 'primary_id';
    const RELATED_ENTITY_ID = 'entity_pk_value';
    const RELATED_ENTITY_TYPE_ID = 'entity_id';
    const REVIEWS_COUNT = 'reviews_count';
    const RATING_SUMMARY = 'rating_summary';
    const STORE_ID = 'store_id';

    /**
     * Set primary id
     *
     * @return int|null
     */
    public function getPrimaryId(): ?int;

    /**
     * Set primary id
     *
     * @param int|null $primaryId
     * @return $this
     */
    public function setPrimaryId(?int $primaryId): ReviewSummaryInterface;

    /**
     * Get id for entity assigned to the review
     *
     * @return int
     */
    public function getRelatedEntityId(): int;

    /**
     * Set id for entity assigned to the review
     *
     * @param int $entityId
     * @return $this
     */
    public function setRelatedEntityId(int $entityId): ReviewSummaryInterface;

    /**
     * Get related entity type id
     *
     * @return int
     */
    public function getRelatedEntityTypeId(): int;

    /**
     * Get review entity id
     *
     * @param int $entityTypeId
     * @return $this
     */
    public function setRelatedEntityTypeId(int $entityTypeId): ReviewSummaryInterface;

    /**
     * Get reviews count
     *
     * @return int
     */
    public function getReviewsCount();

    /**
     * Set review cont
     *
     * @param int $count
     * @return $this
     */
    public function setReviewsCount($count);

    /**
     * Get review summary
     *
     * @return int
     */
    public function getRatingSummary();

    /**
     * Set review summary
     *
     * @param int $summary
     * @return $this
     */
    public function setRatingSummary($summary);

    /**
     * Get store id
     *
     * @return int|null
     */
    public function getStoreId(): ?int;

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId): ReviewSummaryInterface;

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Magento\ReviewApi\Api\Data\ReviewSummaryExtensionInterface
     */
    public function getExtensionAttributes(): \Magento\ReviewApi\Api\Data\ReviewSummaryExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Magento\ReviewApi\Api\Data\ReviewSummaryExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\ReviewApi\Api\Data\ReviewSummaryExtensionInterface $extensionAttributes
    ): ReviewSummaryInterface;
}
