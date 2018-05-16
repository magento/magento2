<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\Source\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Model\SourceValidatorInterface;

/**
 * Check that country id is valid
 */
class CountryValidator implements SourceValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param ValidationResultFactory $validationResultFactory
     */
    public function __construct(ValidationResultFactory $validationResultFactory)
    {
        $this->validationResultFactory = $validationResultFactory;
    }

    /**
     * @inheritdoc
     */
    public function validate(SourceInterface $source): ValidationResult
    {
        $value = (string)$source->getCountryId();

        if ('' === trim($value)) {
            $errors[] = __('"%field" can not be empty.', ['field' => SourceInterface::COUNTRY_ID]);
        } else {
            $errors = [];
        }
        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
