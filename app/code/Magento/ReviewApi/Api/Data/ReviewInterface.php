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
interface ReviewInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const REVIEW_ID = 'review_id';
    const STORE_ID = 'store_id';
    const STORES = 'stores';
    const TITLE = 'title';
    const REVIEW_TEXT = 'detail';
    const CUSTOMER_NICKNAME = 'nickname';
    const CUSTOMER_ID = 'customer_id';
    const RATINGS = 'ratings';
    const REVIEW_ENTITY_ID = 'entity_id';
    const RELATED_ENTITY_ID = 'entity_pk_value';
    const STATUS = 'status_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get review id
     *
     * @return int|null
     */
    public function getReviewId();

    /**
     * Set review id
     *
     * @param int|null $reviewId
     * @return $this
     */
    public function setReviewId($reviewId): ReviewInterface;

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
    public function setStoreId($storeId): ReviewInterface;

    /**
     * Get stores
     *
     * @return int[]
     */
    public function getStores(): array;

    /**
     * Set stores
     *
     * @param int[] $stores
     * @return $this
     */
    public function setStores(array $stores): ReviewInterface;

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): ReviewInterface;

    /**
     * Get review text
     *
     * @return string
     */
    public function getReviewText(): string;

    /**
     * Set review text
     *
     * @param string $text
     * @return $this
     */
    public function setReviewText(string $text): ReviewInterface;

    /**
     * Get customer nickname
     *
     * @return string
     */
    public function getCustomerNickname(): string;

    /**
     * Set customer nickname
     *
     * @param string $nickname
     * @return $this
     */
    public function setCustomerNickname(string $nickname): ReviewInterface;

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getCustomerId(): ?int;

    /**
     * Set customer id
     *
     * @param int|null $customerId
     * @return $this
     */
    public function setCustomerId(?int $customerId): ReviewInterface;

    /**
     * Get review entity id
     *
     * @return int
     */
    public function getReviewEntityId(): int;

    /**
     * Set review entity id
     *
     * @param int $entityId
     * @return ReviewInterface
     */
    public function setReviewEntityId(int $entityId): ReviewInterface;

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
    public function setRelatedEntityId(int $entityId): ReviewInterface;

    /**
     * Get status
     *
     * Possible values: 1 - Approved, 2 - Pending, 3 - Not Approved
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Set status
     *
     * Possible values: 1 - Approved, 2 - Pending, 3 - Not Approved
     *
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status): ReviewInterface;

    /**
     * Get created at
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set created at
     *
     * @param string|null $createdAt
     * @return $this
     */
    public function setCreatedAt(?string $createdAt): ReviewInterface;

    /**
     * Get updated at
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * Set update at
     *
     * @param string|null $updatedAt
     * @return $this
     */
    public function setUpdatedAt(?string $updatedAt): ReviewInterface;

    /**
     * Get review ratings
     *
     * @return \Magento\ReviewApi\Api\Data\RatingOptionVoteInterface[]
     */
    public function getRatings(): array;

    /**
     * Set review ratings
     *
     * @param \Magento\ReviewApi\Api\Data\RatingOptionVoteInterface[] $ratings
     * @return $this
     */
    public function setRatings(array $ratings): ReviewInterface;

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Magento\ReviewApi\Api\Data\ReviewExtensionInterface
     */
    public function getExtensionAttributes(): \Magento\ReviewApi\Api\Data\ReviewExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Magento\ReviewApi\Api\Data\ReviewExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\ReviewApi\Api\Data\ReviewExtensionInterface $extensionAttributes
    ): ReviewInterface;
}
