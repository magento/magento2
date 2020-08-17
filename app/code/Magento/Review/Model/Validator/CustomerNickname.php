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
 * Review customer nickname validator
 */
class CustomerNickname implements ReviewValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * CustomerNickname constructor
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
        if (!\Zend_Validate::is($review->getCustomerNickname(), 'NotEmpty')) {
            $errors[] = __('Nickname can not be empty.');
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
