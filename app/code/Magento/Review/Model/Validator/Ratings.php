<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Model\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Review\Model\Review\RatingsProcessorInterface;
use Magento\ReviewApi\Api\Data\ReviewInterface;
use Magento\ReviewApi\Model\ReviewValidatorInterface;

/**
 * Ratings validator
 */
class Ratings implements ReviewValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var RatingsProcessorInterface
     */
    private $reviewRatingsProcessor;

    /**
     * Store constructor
     *
     * @param ValidationResultFactory $validationResultFactory
     * @param RatingsProcessorInterface $ratingsProcessor
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        RatingsProcessorInterface $ratingsProcessor
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->reviewRatingsProcessor = $ratingsProcessor;
    }

    /**
     * @inheritdoc
     */
    public function validate(ReviewInterface $review): ValidationResult
    {
        $errors = [];
        foreach ($review->getRatings() as $rating) {
            $ratingId = $this->reviewRatingsProcessor->getRatingIdByName(
                $rating->getRatingName(),
                $review->getStoreId()
            );
            if (!$ratingId) {
                $errors[] = __(
                    'Invalid rating name "%1" for store %2',
                    $rating->getRatingName(),
                    $review->getStoreId()
                );
            }

            $optionId = $this->reviewRatingsProcessor->getOptionIdByRatingIdAndValue(
                $ratingId,
                $rating->getRatingValue()
            );
            if (!$optionId) {
                $errors[] = __('Invalid value for rating "%1"', $rating->getRatingName());
            }
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
