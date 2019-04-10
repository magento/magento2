<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;

/**
 * Chain of validators. Extension point for new validators via di configuration
 *
 * @api
 */
class PartialInventoryTransferValidatorChain implements PartialInventoryTransferValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var PartialInventoryTransferValidatorInterface[]
     */
    private $validators;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param PartialInventoryTransferValidatorInterface[] $validators
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        array $validators = []
    ) {
        $this->validationResultFactory = $validationResultFactory;

        foreach ($validators as $validator) {
            if (!$validator instanceof PartialInventoryTransferValidatorInterface) {
                throw new LocalizedException(
                    __('Source Validator must implement PartialInventoryTransferValidatorInterface.')
                );
            }
        }
        $this->validators = $validators;
    }

    /**
     * @inheritdoc
     */
    public function validate(string $originSourceCode, string $destinationSourceCode, array $items): ValidationResult
    {
        $errors = [];
        foreach ($this->validators as $validator) {
            $validationResult = $validator->validate($originSourceCode, $destinationSourceCode, $items);

            if (!$validationResult->isValid()) {
                $errors = array_merge($errors, $validationResult->getErrors());
            }
        }
        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
