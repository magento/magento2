<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Model\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\ReviewApi\Api\Data\ReviewInterface;
use Magento\ReviewApi\Model\ReviewValidatorChain;
use Magento\ReviewApi\Model\ReviewValidatorInterface;

/**
 * Multiple reviews validation
 */
class ReviewsValidator
{
    /**
     * @var ReviewValidatorChain
     */
    private $reviewValidatorChain;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * ReviewsValidator constructor
     *
     * @param ValidationResultFactory $validationResultFactory
     * @param ReviewValidatorInterface $reviewValidatorChain
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        ReviewValidatorInterface $reviewValidatorChain
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->reviewValidatorChain = $reviewValidatorChain;
    }

    /**
     * Validate reviews
     *
     * @param ReviewInterface[] $reviews
     * @return ValidationResult
     */
    public function validate(array $reviews): ValidationResult
    {
        $errors = [[]];
        foreach ($reviews as $review) {
            $validationResult = $this->reviewValidatorChain->validate($review);
            if (!$validationResult->isValid()) {
                $errors[] = $validationResult->getErrors();
            }
        }
        $errors = array_merge(...$errors);

        $validationResult = $this->validationResultFactory->create(['errors' => $errors]);
        return $validationResult;
    }
}
