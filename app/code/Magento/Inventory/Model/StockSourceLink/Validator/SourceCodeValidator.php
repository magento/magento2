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
 * Check that source code is valid
 */
class SourceCodeValidator implements StockSourceLinkValidatorInterface
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
        $value = (string)$link->getSourceCode();

        if ('' === trim($value)) {
            $errors[] = __('"%field" can not be empty.', ['field' => StockSourceLinkInterface::SOURCE_CODE]);
        } elseif (preg_match('/\s/', $value)) {
            $errors[] = __('"%field" can not contain whitespaces.', ['field' => StockSourceLinkInterface::SOURCE_CODE]);
        } else {
            $errors = [];
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
