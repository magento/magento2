<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Catalog\Model\Product\Attribute\Backend\Sku;

/**
 * Product import model validator
 *
 * @api
 * @since 100.0.2
 */
class Validator extends AbstractValidator implements RowValidatorInterface
{
    /**
     * @var RowValidatorInterface[]|AbstractValidator[]
     */
    protected $validators = [];

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    protected $context;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var array
     */
    protected $_uniqueAttributes;

    /**
     * @var array
     */
    protected $_rowData;

    /**
     * @var string|null
     * @since 100.1.0
     */
    protected $invalidAttribute;

    /**
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param RowValidatorInterface[] $validators
     */
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $string,
        $validators = []
    ) {
        $this->string = $string;
        $this->validators = $validators;
    }

    /**
     * Text validation
     *
     * @param mixed $attrCode
     * @param string $type
     * @return bool
     */
    protected function textValidation($attrCode, $type)
    {
        $val = $this->string->cleanString($this->_rowData[$attrCode]);
        if ($type == 'text') {
            $valid = $this->string->strlen($val) < Product::DB_MAX_TEXT_LENGTH;
        } elseif ($attrCode == Product::COL_SKU) {
            $valid = $this->string->strlen($val) <= SKU::SKU_MAX_LENGTH;
            if ($this->string->strlen($val) !== $this->string->strlen(trim($val))) {
                $this->_addMessages([RowValidatorInterface::ERROR_SKU_MARGINAL_WHITESPACES]);
                return false;
            }
        } else {
            $valid = $this->string->strlen($val) < Product::DB_MAX_VARCHAR_LENGTH;
        }
        if (!$valid) {
            $this->_addMessages([RowValidatorInterface::ERROR_EXCEEDED_MAX_LENGTH]);
        }
        return $valid;
    }

    /**
     * Check if value is valid attribute option
     *
     * @param string $attrCode
     * @param array $possibleOptions
     * @param string $value
     * @return bool
     */
    private function validateOption($attrCode, $possibleOptions, $value)
    {
        if (!isset($possibleOptions[strtolower($value)])) {
            $this->_addMessages(
                [
                    sprintf(
                        $this->context->retrieveMessageTemplate(
                            RowValidatorInterface::ERROR_INVALID_ATTRIBUTE_OPTION
                        ),
                        $attrCode
                    )
                ]
            );
            return false;
        }
        return true;
    }

    /**
     * Numeric validation
     *
     * @param mixed $attrCode
     * @param string $type
     * @return bool
     */
    protected function numericValidation($attrCode, $type)
    {
        $val = trim($this->_rowData[$attrCode] ?? '');
        if ($type == 'int') {
            $valid = (string)(int)$val === $val;
        } else {
            $valid = is_numeric($val);
        }
        if (!$valid) {
            $this->_addMessages(
                [
                    sprintf(
                        $this->context->retrieveMessageTemplate(RowValidatorInterface::ERROR_INVALID_ATTRIBUTE_TYPE),
                        $attrCode,
                        $type
                    )
                ]
            );
        }
        return $valid;
    }

    /**
     * Is required attribute valid
     *
     * @param string $attrCode
     * @param array $attributeParams
     * @param array $rowData
     * @return bool
     */
    public function isRequiredAttributeValid($attrCode, array $attributeParams, array $rowData)
    {
        $doCheck = false;
        if ($attrCode == Product::COL_SKU) {
            $doCheck = true;
        } elseif ($attrCode == 'price') {
            $doCheck = false;
        } elseif ($attributeParams['is_required'] && $this->getRowScope($rowData) == Product::SCOPE_DEFAULT
            && $this->context->getBehavior() != \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE
        ) {
            $doCheck = true;
        }

        if ($doCheck === true) {
            return isset($rowData[$attrCode])
                && strlen(trim($rowData[$attrCode]))
                && trim($rowData[$attrCode]) !== $this->context->getEmptyAttributeValueConstant();
        }
        return true;
    }

    /**
     * Is attribute valid
     *
     * @param string $attrCode
     * @param array $attrParams
     * @param array $rowData
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function isAttributeValid($attrCode, array $attrParams, array $rowData)
    {
        $this->_rowData = $rowData;
        if (isset($rowData['product_type']) && !empty($attrParams['apply_to'])
            && !in_array($rowData['product_type'], $attrParams['apply_to'])
        ) {
            return true;
        }

        if (!$this->isRequiredAttributeValid($attrCode, $attrParams, $rowData)) {
            $valid = false;
            $this->_addMessages(
                [
                    sprintf(
                        $this->context->retrieveMessageTemplate(
                            RowValidatorInterface::ERROR_VALUE_IS_REQUIRED
                        ),
                        $attrCode
                    )
                ]
            );
            return $valid;
        }

        if (is_array($rowData[$attrCode])) {
            if (empty($rowData[$attrCode])) {
                return true;
            }

            foreach ($rowData[$attrCode] as $attrValue) {
                if ($attrValue === null || trim($attrValue) === '') {
                    return true;
                }
            }
        } else {
            if ($rowData[$attrCode] === null || trim($rowData[$attrCode]) === '') {
                return true;
            }

            if ($rowData[$attrCode] === $this->context->getEmptyAttributeValueConstant()
                && !$attrParams['is_required']) {
                return true;
            }
        }

        $valid = $this->validateByAttributeType($attrCode, $attrParams, $rowData);

        if ($valid && !empty($attrParams['is_unique'])) {
            if (isset($this->_uniqueAttributes[$attrCode][$rowData[$attrCode]])
                && ($this->_uniqueAttributes[$attrCode][$rowData[$attrCode]] != $rowData[Product::COL_SKU])) {
                $this->_addMessages([RowValidatorInterface::ERROR_DUPLICATE_UNIQUE_ATTRIBUTE]);
                return false;
            }
            $this->_uniqueAttributes[$attrCode][$rowData[$attrCode]] = $rowData[Product::COL_SKU];
        }

        if (!$valid) {
            $this->setInvalidAttribute($attrCode);
        }

        return (bool)$valid;
    }

    /**
     * Validates attribute type.
     *
     * @param string $attrCode
     * @param array $attrParams
     * @param array $rowData
     * @return bool
     */
    private function validateByAttributeType(string $attrCode, array $attrParams, array $rowData): bool
    {
        return match ($attrParams['type']) {
            'varchar', 'text' => $this->textValidation($attrCode, $attrParams['type']),
            'decimal', 'int' => $this->numericValidation($attrCode, $attrParams['type']),
            'select', 'boolean' => $this->validateOption($attrCode, $attrParams['options'], $rowData[$attrCode]),
            'multiselect' => $this->validateMultiselect($attrCode, $attrParams['options'], $rowData[$attrCode]),
            'datetime' => $this->validateDateTime($rowData[$attrCode]),
            default => true,
        };
    }

    /**
     * Validate multiselect attribute.
     *
     * @param string $attrCode
     * @param array $options
     * @param array|string $rowData
     * @return bool
     */
    private function validateMultiselect(string $attrCode, array $options, array|string $rowData): bool
    {
        $valid = true;

        $values = $this->context->parseMultiselectValues($rowData);
        foreach ($values as $value) {
            $valid = $this->validateOption($attrCode, $options, $value);
            if (!$valid) {
                break;
            }
        }

        $uniqueValues = array_unique($values);
        if (count($uniqueValues) != count($values)) {
            $valid = false;
            $this->_addMessages([RowValidatorInterface::ERROR_DUPLICATE_MULTISELECT_VALUES]);
        }

        return $valid;
    }

    /**
     * Validate datetime attribute.
     *
     * @param string $rowData
     * @return bool
     */
    private function validateDateTime(string $rowData): bool
    {
        $val = trim($rowData);
        $valid = strtotime($val) !== false;
        if (!$valid) {
            $this->_addMessages([RowValidatorInterface::ERROR_INVALID_ATTRIBUTE_TYPE]);
        }
        return $valid;
    }

    /**
     * Set invalid attribute
     *
     * @param string|null $attribute
     * @return void
     * @since 100.1.0
     */
    protected function setInvalidAttribute($attribute)
    {
        $this->invalidAttribute = $attribute;
    }

    /**
     * Get invalid attribute
     *
     * @return string
     * @since 100.1.0
     */
    public function getInvalidAttribute()
    {
        return $this->invalidAttribute;
    }

    /**
     * Is valid attributes
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function isValidAttributes()
    {
        $this->_clearMessages();
        $this->setInvalidAttribute(null);
        if (!isset($this->_rowData['product_type'])) {
            return false;
        }
        $entityTypeModel = $this->context->retrieveProductTypeByName($this->_rowData['product_type']);
        if ($entityTypeModel) {
            foreach ($this->_rowData as $attrCode => $attrValue) {
                $attrParams = $entityTypeModel->retrieveAttributeFromCache($attrCode);
                if ($attrCode === Product::COL_CATEGORY && $attrValue) {
                    $this->isCategoriesValid($attrValue);
                } elseif ($attrParams) {
                    $this->isAttributeValid($attrCode, $attrParams, $this->_rowData);
                }
            }
            if ($this->getMessages()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        $this->_rowData = $value;
        $this->_clearMessages();
        $returnValue = $this->isValidAttributes();
        foreach ($this->validators as $validator) {
            if (!$validator->isValid($value)) {
                $returnValue = false;
                $this->_addMessages($validator->getMessages());
            }
        }
        return $returnValue;
    }

    /**
     * Obtain scope of the row from row data.
     *
     * @param array $rowData
     * @return int
     */
    public function getRowScope(array $rowData)
    {
        if (empty($rowData[Product::COL_STORE])) {
            return Product::SCOPE_DEFAULT;
        }
        return Product::SCOPE_STORE;
    }

    /**
     * Validate category names
     *
     * @param string|array $value
     * @return bool
     */
    private function isCategoriesValid(string|array $value) : bool
    {
        $result = true;
        if ($value) {
            $values = [];
            if (is_string($value)) {
                $values = explode($this->context->getMultipleValueSeparator(), $value);
            } elseif (is_array($value)) {
                $values = $value;
            }

            foreach ($values as $categoryName) {
                if ($result === true) {
                    $result = $this->string->strlen($categoryName) < Product::DB_MAX_VARCHAR_LENGTH;
                }
            }
        }
        if ($result === false) {
            $this->_addMessages([RowValidatorInterface::ERROR_EXCEEDED_MAX_LENGTH]);
            $this->setInvalidAttribute(Product::COL_CATEGORY);
        }
        return $result;
    }

    /**
     * Init
     *
     * @param \Magento\CatalogImportExport\Model\Import\Product $context
     * @return $this
     */
    public function init($context)
    {
        $this->context = $context;
        foreach ($this->validators as $validator) {
            $validator->init($context);
        }

        return $this;
    }
}
