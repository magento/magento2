<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\StockSourceLink\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryApi\Model\StockSourceLinkValidatorInterface;

/**
 * Check that priority is valid
 */
class PriorityValidator implements StockSourceLinkValidatorInterface
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
    public function validate(StockSourceLinkInterface $link): ValidationResult
    {
        $value = (int)$link->getPriority();

        if ($value <= 0) {
            $errors[] = __('"%field" should be greater then 0.', ['field' => StockSourceLinkInterface::PRIORITY]);
        } else {
            $errors = [];
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
