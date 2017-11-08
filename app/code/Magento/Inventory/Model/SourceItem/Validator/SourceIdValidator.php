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

/**
 * Check that source id is valid
 */
class SourceIdValidator implements SourceItemValidatorInterface
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
    public function validate(SourceItemInterface $source): ValidationResult
    {
        $errors = [];
        if (!is_numeric($source->getSourceId())) {
            $errors[] = __(
                '"%field" should be numeric.',
                ['field' => SourceItemInterface::SOURCE_ID]
            );
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
