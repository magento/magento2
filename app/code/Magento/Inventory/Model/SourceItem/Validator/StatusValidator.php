<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\SourceItem\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Inventory\Model\OptionSource\SourceItemStatus;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

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
     * @var SourceItemStatus
     */
    private $sourceItemStatus;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param SourceItemStatus $sourceItemStatus
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        SourceItemStatus $sourceItemStatus
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->sourceItemStatus = $sourceItemStatus;
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

        $allowedStatuses = array_column($this->sourceItemStatus->toOptionArray(), 'value');

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
