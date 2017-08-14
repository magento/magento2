<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\Source\Validator;

use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Check that name is valid
 */
class NameValidator implements SourceValidatorInterface
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
    public function validate(SourceInterface $source)
    {
        $value = (string)$source->getName();

        if ('' === trim($value)) {
            $errors[] = __('"%field" can not be empty.', ['field' => SourceInterface::NAME]);
        } else {
            $errors = [];
        }
        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
