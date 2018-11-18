<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Model\ResourceModel\Review;

use Magento\Framework\App\ResourceConnection;
use Magento\Review\Model\ResourceModel\Review as ReviewResource;
use Magento\ReviewApi\Api\Data\ReviewInterface;

/**
 * Class DeleteMultiple
 */
class DeleteMultiple
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * DeleteMultiple constructor
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Multiple delete source items
     *
     * @param ReviewInterface[] $reviews
     * @return void
     */
    public function execute(array $reviews)
    {
        if (!count($reviews)) {
            return;
        }

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(ReviewResource::TABLE_NAME_REVIEW);
        $whereSql = $this->buildWhereSqlPart($reviews);
        $connection->delete($tableName, $whereSql);
    }

    /**
     * Build where sql condition
     *
     * @param ReviewInterface[] $reviews
     * @return array
     */
    private function buildWhereSqlPart(array $reviews): array
    {
        $reviewIds = [];
        foreach ($reviews as $review) {
            $reviewIds[] = $review->getReviewId();
        }

        $conditionSql = sprintf('%s IN(?)', ReviewInterface::REVIEW_ID);

        return [$conditionSql => $reviewIds];
    }
}
