<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewApi\Api;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface GetReviewsInterface
{
    /**
     * Get reviews by given SearchCriteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\ReviewApi\Api\Data\ReviewSearchResultsInterface|SearchResultsInterface
     */
    public function execute(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ): SearchResultsInterface;
}
