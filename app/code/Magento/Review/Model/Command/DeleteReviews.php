<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Model\Command;

use Magento\Framework\Exception\InputException;
use Magento\Review\Model\ResourceModel\Review\DeleteMultiple;
use Magento\ReviewApi\Api\DeleteReviewsInterface;
use Magento\ReviewApi\Api\ReviewOperationResponseInterface;
use Magento\ReviewApi\Model\AggregatorInterface;
use Psr\Log\LoggerInterface;

/**
 * Class DeleteReviews
 */
class DeleteReviews implements DeleteReviewsInterface
{
    /**
     * @var DeleteMultiple
     */
    private $deleteMultiple;

    /**
     * @var AggregatorInterface
     */
    private $reviewAggregator;

    /**
     * @var ReviewOperationResponseInterface
     */
    private $reviewOperationResponse;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * DeleteReviews constructor
     *
     * @param DeleteMultiple $deleteMultiple
     * @param AggregatorInterface $reviewAggregator
     * @param ReviewOperationResponseInterface $reviewOperationResponse
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeleteMultiple $deleteMultiple,
        AggregatorInterface $reviewAggregator,
        ReviewOperationResponseInterface $reviewOperationResponse,
        LoggerInterface $logger
    ) {
        $this->deleteMultiple = $deleteMultiple;
        $this->reviewAggregator = $reviewAggregator;
        $this->reviewOperationResponse = $reviewOperationResponse;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $reviews): ReviewOperationResponseInterface
    {
        if (empty($reviews)) {
            throw new InputException(__('Input data is empty'));
        }

        try {
            $this->deleteMultiple->execute($reviews);

            foreach ($reviews as $review) {
                $this->reviewAggregator->aggregate($review);
            }

            $this->reviewOperationResponse->addSuccessfulReviews($reviews);
        } catch (\Exception $e) {
            $this->reviewOperationResponse->addFailedReviews($reviews);
            $this->reviewOperationResponse->addError(
                __('There was an error while deleting the reviews.')
            );
            $this->logger->error($e->getMessage());
        }

        return $this->reviewOperationResponse;
    }
}
