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
 * Check that status is valid
 */
class StatusValidator implements SourceItemValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
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
    public function validate(SourceItemInterface $source): ValidationResult
    {
        $value = $source->getStatus();

        if (!is_numeric($value)) {
            $errors[] = __(
                '"%field" should be numeric.',
                ['field' => SourceItemInterface::STATUS]
            );
            return $this->validationResultFactory->create(['errors' => $errors]);
        }

        $allowedStatuses = [SourceItemInterface::STATUS_IN_STOCK, SourceItemInterface::STATUS_OUT_OF_STOCK];

        $errors = [];
        if (!in_array((int)$value, $allowedStatuses, true)) {
            $errors[] = __(
                '"%field" should a known status.',
                ['field' => SourceItemInterface::STATUS]
            );
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
