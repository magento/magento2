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
interface RatingOptionVoteInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const VOTE_ID = 'vote_id';
    const RATING_OPTION_ID = 'option_id';
    const REMOTE_IP_ADDRESS = 'remote_ip';
    const REMOTE_IP_ADDRESS_LONG = 'remote_ip_long';
    const CUSTOMER_ID = 'customer_id';
    const RELATED_ENTITY_ID = 'entity_pk_value';
    const RATING_ID = 'rating_id';
    const REVIEW_ID = 'review_id';
    const PERCENT = 'percent';
    const RATING_NAME = 'rating_name';
    const RATING_VALUE = 'value';
    const STORE_ID = 'store_id';

    /**
     * Get vote id
     *
     * @return int|null
     */
    public function getVoteId(): ?int;

    /**
     * Set vote id
     *
     * @param int $voteId
     * @return $this
     */
    public function setVoteId(?int $voteId): RatingOptionVoteInterface;

    /**
     * Get rating option id
     *
     * @return int
     */
    public function getRatingOptionId(): ?int;

    /**
     * Set rating option id
     *
     * @param int $ratingOptionId
     * @return $this
     */
    public function setRatingOptionId(int $ratingOptionId): RatingOptionVoteInterface;

    /**
     * Get remote ip address
     *
     * @return string|null
     */
    public function getRemoteIpAddress(): ?string;

    /**
     * Set remote ip address
     *
     * @param string $ipAddress
     * @return $this
     */
    public function setRemoteIpAddress(string $ipAddress): RatingOptionVoteInterface;

    /**
     * Get remote ip address long
     *
     * @return string|null
     */
    public function getRemoteIpAddressLong(): ?string;

    /**
     * Set remote ip address long
     *
     * @param string $ipAddressLong
     * @return $this
     */
    public function setRemoteIpAddressLong(string $ipAddressLong): RatingOptionVoteInterface;

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
    public function setCustomerId(?int $customerId): RatingOptionVoteInterface;

    /**
     * Get id for entity assigned to the review
     *
     * @return int|null
     */
    public function getRelatedEntityId(): ?int;

    /**
     * Set id for entity assigned to the review
     *
     * @param int $entityId
     * @return $this
     */
    public function setRelatedEntityId(int $entityId): RatingOptionVoteInterface;

    /**
     * Get rating id
     *
     * @return int|null
     */
    public function getRatingId(): ?int;

    /**
     * Set rating id
     *
     * @param int $ratingId
     * @return $this
     */
    public function setRatingId(int $ratingId): RatingOptionVoteInterface;

    /**
     * Get review id
     *
     * @return int|null
     */
    public function getReviewId(): ?int;

    /**
     * Set review id
     *
     * @param int $reviewId
     * @return $this
     */
    public function setReviewId(int $reviewId): RatingOptionVoteInterface;

    /**
     * Get rating percent
     *
     * @return float|null
     */
    public function getPercent(): ?float;

    /**
     * Set rating percent
     *
     * @param float $ratingPercent
     * @return $this
     */
    public function setPercent(float $ratingPercent): RatingOptionVoteInterface;

    /**
     * Get rating name
     *
     * @return string|null
     */
    public function getRatingName(): ?string;

    /**
     * Set rating name
     *
     * @param string|null $ratingName
     * @return $this
     */
    public function setRatingName(?string $ratingName): RatingOptionVoteInterface;

    /**
     * Get rating value
     *
     * @return int
     */
    public function getRatingValue(): int;

    /**
     * Set rating value
     *
     * @param int $value
     * @return $this
     */
    public function setRatingValue(int $value): RatingOptionVoteInterface;

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
    public function setStoreId($storeId): RatingOptionVoteInterface;

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Magento\ReviewApi\Api\Data\RatingOptionVoteExtensionInterface
     */
    public function getExtensionAttributes(): \Magento\ReviewApi\Api\Data\RatingOptionVoteExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Magento\ReviewApi\Api\Data\RatingOptionVoteExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\ReviewApi\Api\Data\RatingOptionVoteExtensionInterface $extensionAttributes
    ): RatingOptionVoteInterface;
}
