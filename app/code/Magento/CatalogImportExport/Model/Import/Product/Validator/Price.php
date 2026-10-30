<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Rejects negative values for product price-type fields during import
 */
class Price extends AbstractImportValidator implements RowValidatorInterface
{
    /**
     * Error code for negative price-type values
     */
    public const ERROR_NEGATIVE_PRICE_VALUE = 'invalidNegativePriceValue';

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private ProductAttributeRepositoryInterface $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var string[]
     */
    private array $priceAttributeCodes = [];

    /**
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProductAttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function init($context)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('frontend_input', 'price')
            ->create();
        $attributes = $this->attributeRepository->getList($searchCriteria)->getItems();
        $this->priceAttributeCodes = array_map(
            fn ($attr) => $attr->getAttributeCode(),
            $attributes
        );
        return parent::init($context);
    }

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        $valid = true;
        $emptyConstant = $this->context->getEmptyAttributeValueConstant();

        foreach ($this->priceAttributeCodes as $field) {
            if (!isset($value[$field])) {
                continue;
            }
            $fieldValue = $value[$field];
            if (is_string($fieldValue)) {
                $fieldValue = trim($fieldValue);
            }
            if ($fieldValue === '' || $fieldValue === null || $fieldValue === $emptyConstant) {
                continue;
            }
            if (is_numeric($fieldValue) && (float) $fieldValue < 0) {
                $this->_addMessages([(string) __(
                    'The %fieldName value of "%value" must be greater than or equal to %minValue.',
                    ['fieldName' => $field, 'value' => $fieldValue, 'minValue' => 0]
                )]);
                $valid = false;
            }
        }

        return $valid;
    }
}
