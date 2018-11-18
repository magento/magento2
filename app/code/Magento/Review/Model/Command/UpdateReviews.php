<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Model\Command;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Review\Model\ResourceModel\Review\UpdateMultiple;
use Magento\ReviewApi\Api\Data\ReviewInterface;
use Magento\ReviewApi\Api\ReviewOperationResponseInterface;
use Magento\ReviewApi\Api\UpdateReviewsInterface;
use Magento\ReviewApi\Model\AggregatorInterface;
use Magento\ReviewApi\Model\ReviewValidatorChain;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateReviews
 */
class UpdateReviews implements UpdateReviewsInterface
{
    /**
     * @var UpdateMultiple
     */
    private $updateMultiple;

    /**
     * @var ReviewValidatorChain
     */
    private $reviewValidatorChain;

    /**
     * @var AggregatorInterface
     */
    private $reviewAggregator;

    /**
     * @var ReviewOperationResponseInterface
     */
    private $reviewOperationResponse;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * UpdateReviews constructor
     *
     * @param UpdateMultiple $updateMultiple
     * @param ReviewValidatorChain $reviewValidatorChain
     * @param AggregatorInterface $reviewAggregator
     * @param ReviewOperationResponseInterface $reviewOperationResponse
     * @param SerializerInterface $jsonSerializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        UpdateMultiple $updateMultiple,
        ReviewValidatorChain $reviewValidatorChain,
        AggregatorInterface $reviewAggregator,
        ReviewOperationResponseInterface $reviewOperationResponse,
        SerializerInterface $jsonSerializer,
        LoggerInterface $logger
    ) {
        $this->updateMultiple = $updateMultiple;
        $this->reviewValidatorChain = $reviewValidatorChain;
        $this->reviewAggregator = $reviewAggregator;
        $this->reviewOperationResponse = $reviewOperationResponse;
        $this->jsonSerializer = $jsonSerializer;
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

        $validReviews = [];
        $invalidReviews = [];

        foreach ($reviews as $key => $review) {
            $validationResult = $this->reviewValidatorChain->validate($review);
            if (!$validationResult->isValid()) {
                $invalidReviews[] = $review;

                $this->reviewOperationResponse->addError(
                    $this->getErrorMessage($validationResult->getErrors(), $review)
                );
                continue;
            }
            $validReviews[] = $review;
        }

        if (!empty($validReviews)) {
            try {
                $this->updateMultiple->execute($validReviews);

                foreach ($validReviews as $review) {
                    $this->reviewAggregator->aggregate($review);
                }

                $this->reviewOperationResponse->addSuccessfulReviews($validReviews);
            } catch (\Exception $e) {
                $this->reviewOperationResponse->addFailedReviews($validReviews);
                $this->reviewOperationResponse->addError(
                    __('There was an error while saving the reviews.')
                );
                $this->logger->error($e->getMessage());
            }
        }
        if (!empty($invalidReviews)) {
            $this->reviewOperationResponse->addFailedReviews($invalidReviews);
        }

        unset($validReviews);
        unset($invalidReviews);

        return $this->reviewOperationResponse;
    }

    /**
     * Get error message
     *
     * @param array $errors
     * @param ReviewInterface $review
     * @return string
     */
    private function getErrorMessage(array $errors, ReviewInterface $review)
    {
        $errorMessage = sprintf(
            '%s => Request Data: %s:',
            implode(',', $errors),
            $this->jsonSerializer->serialize($review->toArray())
        );

        return $errorMessage;
    }
}
