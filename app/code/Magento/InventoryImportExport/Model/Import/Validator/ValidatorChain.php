<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Model\Import\Validator;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validation\ValidationResultFactory;

/**
 * @inheritdoc
 */
class ValidatorChain implements ValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var ValidatorInterface[]
     */
    private $validators;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param array $validators
     * @throws LocalizedException
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        array $validators = []
    ) {
        $this->validationResultFactory = $validationResultFactory;

        foreach ($validators as $validator) {
            if (!$validator instanceof ValidatorInterface) {
                throw new LocalizedException(
                    __('Row Validator must implement %interface.', ['interface' => ValidatorInterface::class])
                );
            }
        }
        $this->validators = $validators;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $rowData, int $rowNumber)
    {
        /* the inner empty array covers cases when no loops were made */
        $errors = [[]];
        foreach ($this->validators as $validator) {
            $validationResult = $validator->validate($rowData, $rowNumber);

            if (!$validationResult->isValid()) {
                $errors[] = $validationResult->getErrors();
            }
        }

        return $this->validationResultFactory->create(['errors' => array_merge(...$errors)]);
    }
}
