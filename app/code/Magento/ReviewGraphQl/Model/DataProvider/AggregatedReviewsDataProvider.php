<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewGraphQl\Model\DataProvider;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Review\Model\ResourceModel\Review\Collection as ReviewCollection;
use Magento\Review\Model\ResourceModel\Review\Product\Collection as ProductCollection;
use Magento\ReviewGraphQl\Mapper\ReviewDataMapper;

/**
 * Provides aggregated reviews result
 *
 * The following class prepares the GraphQl endpoints' result for Customer and Product reviews
 */
class AggregatedReviewsDataProvider
{
    /**
     * @var ReviewDataMapper
     */
    private $reviewDataMapper;

    /**
     * @param ReviewDataMapper $reviewDataMapper
     */
    public function __construct(ReviewDataMapper $reviewDataMapper)
    {
        $this->reviewDataMapper = $reviewDataMapper;
    }

    /**
     * Get reviews result
     *
     * @param ProductCollection|ReviewCollection $reviewsCollection
     *
     * @return array
     */
    public function getData($reviewsCollection): array
    {
        if ($reviewsCollection->getPageSize()) {
            $maxPages = ceil($reviewsCollection->getSize() / $reviewsCollection->getPageSize());
        } else {
            $maxPages = 0;
        }

        $currentPage = $reviewsCollection->getCurPage();
        if ($reviewsCollection->getCurPage() > $maxPages && $reviewsCollection->getSize() > 0) {
            $currentPage = new GraphQlInputException(
                __(
                    'currentPage value %1 specified is greater than the number of pages available.',
                    [$maxPages]
                )
            );
        }

        $items = [];
        foreach ($reviewsCollection->getItems() as $item) {
            $items[] = $this->reviewDataMapper->map($item);
        }

        return [
            'total_count' => $reviewsCollection->getSize(),
            'items' => $items,
            'page_info' => [
                'page_size' => $reviewsCollection->getPageSize(),
                'current_page' => $currentPage,
                'total_pages' => $maxPages
            ]
        ];
    }
}
