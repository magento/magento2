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
 * Responsible for Stock Source links validation
 */
class StockSourceLinksValidator
{
    /**
     * @var StockSourceLinkValidatorInterface
     */
    private $stockSourceLinkValidator;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param StockSourceLinkValidatorInterface $stockSourceLinkValidator
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        StockSourceLinkValidatorInterface $stockSourceLinkValidator
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->stockSourceLinkValidator = $stockSourceLinkValidator;
    }

    /**
     * @param StockSourceLinkInterface[] $links
     * @return ValidationResult
     */
    public function validate(array $links): ValidationResult
    {
        $errors = [[]];
        foreach ($links as $sourceItem) {
            $validationResult = $this->stockSourceLinkValidator->validate($sourceItem);
            if (!$validationResult->isValid()) {
                $errors[] = $validationResult->getErrors();
            }
        }
        $errors = array_merge(...$errors);

        $validationResult = $this->validationResultFactory->create(['errors' => $errors]);
        return $validationResult;
    }
}
