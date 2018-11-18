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
interface RatingInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const RATING_ID = 'rating_id';
    const RATING_ENTITY_ID = 'entity_id';
    const RATING_NAME = 'rating_code';
    const RATING_NAMES = 'rating_codes';
    const POSITION = 'position';
    const IS_ACTIVE = 'is_active';
    const STORE_ID = 'store_id';
    const STORES = 'stores';
    const RATING_OPTIONS = 'options';

    /**
     * Get rating id
     *
     * @return int|null
     */
    public function getRatingId(): ?int;

    /**
     * Set rating id
     *
     * @param int|null $ratingId
     * @return $this
     */
    public function setRatingId(?int $ratingId): RatingInterface;

    /**
     * Get rating entity id
     *
     * @return int|null
     */
    public function getRatingEntityId(): int;

    /**
     * Set rating entity id
     *
     * @param int|null $entityId
     * @return $this
     */
    public function setRatingEntityId(int $entityId): RatingInterface;

    /**
     * Get rating name
     *
     * @return string
     */
    public function getRatingName(): string;

    /**
     * Set rating name
     *
     * @param string $ratingName
     * @return $this
     */
    public function setRatingName(string $ratingName): RatingInterface;

    /**
     * Get rating names
     *
     * @return string[]
     */
    public function getRatingNames(): ?array;

    /**
     * Set rating names
     *
     * @param string[] $ratingNames
     * @return $this
     */
    public function setRatingNames(?array $ratingNames): RatingInterface;

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition(): int;

    /**
     * Set position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition(int $position): RatingInterface;

    /**
     * Is active
     *
     * @return bool
     */
    public function isActive(): bool;

    /**
     * Set is active
     *
     * Note: Can not force boolean due to reverse compatibility
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive): RatingInterface;

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
    public function setStoreId($storeId): RatingInterface;

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
    public function setStores(array $stores): RatingInterface;

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions();

    /**
     * Set options
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Magento\ReviewApi\Api\Data\RatingExtensionInterface
     */
    public function getExtensionAttributes(): \Magento\ReviewApi\Api\Data\RatingExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Magento\ReviewApi\Api\Data\RatingExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\ReviewApi\Api\Data\RatingExtensionInterface $extensionAttributes
    ): RatingInterface;
}
