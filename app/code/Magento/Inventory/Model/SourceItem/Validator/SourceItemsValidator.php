<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\SourceItem\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Model\SourceItemValidatorInterface;

/**
 * Responsible for Source items validation
 */
class SourceItemsValidator
{
    /**
     * @var SourceItemValidatorInterface
     */
    private $sourceItemValidator;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param SourceItemValidatorInterface $sourceItemValidator
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        SourceItemValidatorInterface $sourceItemValidator
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->sourceItemValidator = $sourceItemValidator;
    }

    /**
     * @param SourceItemInterface[] $sourceItems
     * @return ValidationResult
     */
    public function validate(array $sourceItems): ValidationResult
    {
        $errors = [[]];
        foreach ($sourceItems as $sourceItem) {
            $validationResult = $this->sourceItemValidator->validate($sourceItem);
            if (!$validationResult->isValid()) {
                $errors[] = $validationResult->getErrors();
            }
        }
        $errors = array_merge(...$errors);

        $validationResult = $this->validationResultFactory->create(['errors' => $errors]);
        return $validationResult;
    }
}
