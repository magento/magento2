<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewGraphQl\Mapper;

use Magento\Catalog\Model\Product;
use Magento\Review\Model\Review;

/**
 * Converts the review data from review object to an associative array
 */
class ReviewDataMapper
{
    /**
     * Mapping the review data
     *
     * @param Review|Product $review
     *
     * @return array
     */
    public function map($review): array
    {
        return [
            'summary' => $review->getData('title'),
            'text' => $review->getData('detail'),
            'nickname' => $review->getData('nickname'),
            'created_at' => $review->getData('created_at'),
            'rating_votes' => $review->getData('rating_votes'),
            'sku' => $review->getSku()
        ];
    }
}
