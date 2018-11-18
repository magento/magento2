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
use Magento\ReviewApi\Model\ReviewValidatorInterface;

/**
 * Store validator
 */
class Store implements ReviewValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * Store constructor
     *
     * @param ValidationResultFactory $validationResultFactory
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory
    ) {
        $this->validationResultFactory = $validationResultFactory;
    }

    /**
     * @inheritdoc
     */
    public function validate(ReviewInterface $review): ValidationResult
    {
        $errors = [];
        if (!is_numeric($review->getStoreId())) {
            $errors[] = __('Store ID can not be empty.');
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
