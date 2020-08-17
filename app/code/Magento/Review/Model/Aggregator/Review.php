<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Model\Aggregator;

use Magento\Review\Model\ResourceModel\Review as ReviewResource;
use Magento\ReviewApi\Api\Data\ReviewInterface;
use Magento\ReviewApi\Model\AggregatorInterface;

/**
 * Class Review
 */
class Review implements AggregatorInterface
{
    /**
     * @var ReviewResource
     */
    private $reviewResource;

    /**
     * Aggregator constructor
     *
     * @param ReviewResource $reviewResource
     */
    public function __construct(
        ReviewResource $reviewResource
    ) {
        $this->reviewResource = $reviewResource;
    }

    /**
     * @inheritdoc
     */
    public function aggregate(ReviewInterface $review): void
    {
        $this->reviewResource->aggregate($review);
    }
}
