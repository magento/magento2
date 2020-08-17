<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewApi\Model;

use Magento\ReviewApi\Api\Data\ReviewInterface;

/**
 * Interface for review aggregator
 *
 * @api
 */
interface AggregatorInterface
{
    /**
     * Aggregate review
     *
     * @param ReviewInterface $review
     * @return void
     */
    public function aggregate(ReviewInterface $review): void;
}
