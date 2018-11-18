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
interface RatingOptionInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const OPTION_ID = 'option_id';
    const RATING_ID = 'rating_id';
    const CODE = 'code';
    const VALUE = 'value';
    const POSITION = 'position';

    /**
     * Get option id
     *
     * @return int|null
     */
    public function getOptionId();

    /**
     * Set option id
     *
     * @param int $optionId
     * @return $this
     */
    public function setOptionId($optionId): RatingOptionInterface;

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
    public function setRatingId(int $ratingId): RatingOptionInterface;

    /**
     * Get code
     *
     * @return int
     */
    public function getCode(): int;

    /**
     * Set code
     *
     * @param int $code
     * @return $this
     */
    public function setCode(int $code): RatingOptionInterface;

    /**
     * Get rating value
     *
     * @return int
     */
    public function getValue(): int;

    /**
     * Set rating value
     *
     * @param int $value
     * @return $this
     */
    public function setValue(int $value): RatingOptionInterface;

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
    public function setPosition(int $position): RatingOptionInterface;

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Magento\ReviewApi\Api\Data\RatingOptionExtensionInterface
     */
    public function getExtensionAttributes(): \Magento\ReviewApi\Api\Data\RatingOptionExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Magento\ReviewApi\Api\Data\RatingOptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\ReviewApi\Api\Data\RatingOptionExtensionInterface $extensionAttributes
    ): RatingOptionInterface;
}
