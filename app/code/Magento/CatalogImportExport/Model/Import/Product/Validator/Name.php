<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product\SkuStorage;
use Magento\CatalogImportExport\Model\Import\Product;

class Name extends AbstractImportValidator implements RowValidatorInterface
{
    /**
     * @var SkuStorage
     */
    private SkuStorage $skuStorage;

    /**
     * @param SkuStorage $skuStorage
     */
    public function __construct(SkuStorage $skuStorage)
    {
        $this->skuStorage = $skuStorage;
        $this->fieldName = Product::COL_NAME;
    }

    /**
     * @inheritDoc
     */
    public function isValid($value)
    {
        $this->_clearMessages();

        $skuExists = $this->skuStorage->has($value[Product::COL_SKU]);
        $hasCustomOptions = $this->hasCustomOptions($value);
        $hasNameValue = $this->hasNameValue($value);
        $hasParentInImport = $this->hasValidatedImportParent($value);

        if (!$skuExists && !$hasCustomOptions && !$hasNameValue && !$hasParentInImport) {
            return $this->invalidate();
        }

        return true;
    }

    /**
     * Invalidate row data
     *
     * @return bool
     */
    private function invalidate(): bool
    {
        $this->_addMessages(
            [
                sprintf(
                    $this->context->retrieveMessageTemplate(self::ERROR_INVALID_ATTRIBUTE_TYPE),
                    $this->fieldName,
                    'not empty'
                )
            ]
        );
        return false;
    }

    /**
     * Check if current row data has a soon-to-be added parent product
     *
     * @param array $rowData
     * @return bool
     */
    private function hasValidatedImportParent(array $rowData): bool
    {
        return array_key_exists('has_import_parent', $rowData) && $rowData['has_import_parent'];
    }

    /**
     * Check if row data contains name value
     *
     * @param array $rowData
     * @return bool
     */
    private function hasNameValue(array $rowData): bool
    {
        return array_key_exists($this->fieldName, $rowData) &&
            !empty($rowData[$this->fieldName]) &&
            $rowData[$this->fieldName] !== $this->context->getEmptyAttributeValueConstant();
    }

    /**
     * Check if import data contains custom options
     *
     * @param array $rowData
     * @return bool
     */
    private function hasCustomOptions(array $rowData): bool
    {
        return array_key_exists('custom_options', $rowData) &&
            !empty($rowData['custom_options']) &&
            $rowData['custom_options'] !== $this->context->getEmptyAttributeValueConstant();
    }
}
