<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\GraphQl\Query\Fields;
use Magento\Quote\Model\Quote\Config as QuoteConfig;

/**
 * Class for extending product attributes for quote.
 */
class ProductAttributesExtender
{
    /**
     * Validation pattern for attribute code
     */
    private const VALIDATION_RULE_PATTERN = '/^[a-zA-Z]+[a-zA-Z0-9_]*$/u';

    private const ATTRIBUTE_CODE_MAX_LENGTH = 60;

    private const ATTRIBUTE_CODE_MIN_LENGTH = 1;

    /**
     * @var Fields
     */
    private $fields;

    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var string
     */
    private $fieldsHash = '';

    /**
     * @var array
     */
    private $attributes;

    /**
     * @param Fields $fields
     * @param AttributeCollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        Fields $fields,
        AttributeCollectionFactory $attributeCollectionFactory
    ) {
        $this->fields = $fields;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * Get only attribute codes that pass validation
     *
     * @return array
     */
    private function getValidAttributeCodes(): array
    {
        return array_filter($this->fields->getFieldsUsedInQuery(), [$this,'validateAttributeCode']);
    }

    /**
     * Validate attribute code
     *
     * @param string|int $attributeCode
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function validateAttributeCode(string|int $attributeCode): bool
    {
        $attributeCode = trim((string)$attributeCode);
        if (strlen($attributeCode) > 0
            && !preg_match(self::VALIDATION_RULE_PATTERN, $attributeCode)
        ) {
            return false;
        }

        $minLength = self::ATTRIBUTE_CODE_MIN_LENGTH;
        $maxLength = self::ATTRIBUTE_CODE_MAX_LENGTH;

        $isAllowedLength = filter_var(
            strlen($attributeCode),
            FILTER_VALIDATE_INT,
            ['options' => [
                'min_range' => $minLength, 'max_range' => $maxLength]
            ]
        );

        if (!$isAllowedLength) {
            return false;
        }

        return true;
    }

    /**
     * Get attributes based on validated codes
     *
     * @return array
     */
    private function getAttributes(): array
    {
        $attributeCollection = $this->attributeCollectionFactory->create()
            ->removeAllFieldsFromSelect()
            ->addFieldToSelect('attribute_code')
            ->setCodeFilter($this->getValidAttributeCodes())
            ->load();
        return $attributeCollection->getColumnValues('attribute_code');
    }

    /**
     * Add requested product attributes.
     *
     * @param QuoteConfig $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductAttributes(QuoteConfig $subject, array $result): array
    {
        $hash = hash('sha256', json_encode($this->fields->getFieldsUsedInQuery()));
        if (!$this->fieldsHash || $this->fieldsHash !== $hash) {
            $this->fieldsHash = hash('sha256', json_encode($this->fields->getFieldsUsedInQuery()));
            $this->attributes = $this->getAttributes();
        }
        $attributes = $this->attributes;

        return array_unique(array_merge($result, $attributes));
    }
}
