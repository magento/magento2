<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Model\Review;

/**
 * Class RatingsProcessor
 *
 * @package Magento_Review
 */
interface RatingsProcessorInterface
{
    /**
     * Get rating id by name
     *
     * @param string $ratingName
     * @param int $storeId
     * @return int|null
     */
    public function getRatingIdByName(string $ratingName, int $storeId): ?int;

    /**
     * Get rating option id by rating id and value
     *
     * @param int $ratingId
     * @param int $value
     * @return int|null
     */
    public function getOptionIdByRatingIdAndValue(int $ratingId, int $value): ?int;
}
