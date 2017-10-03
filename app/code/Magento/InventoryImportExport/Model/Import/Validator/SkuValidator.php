<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryImportExport\Model\Import\Validator;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryImportExport\Model\Import\Sources;

/**
 * Extension point for row validation
 *
 * @api
 */
class SkuValidator implements ValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @throws LocalizedException
     */
    public function __construct(ValidationResultFactory $validationResultFactory)
    {
        $this->validationResultFactory = $validationResultFactory;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $rowData, $rowNumber)
    {
        $errors = [];

        if (!isset($rowData[Sources::COL_SKU])) {
            $errors[] = __('Missing required column "%column"', ['column' => Sources::COL_SKU]);
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
