<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;

/**
 * Class \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\TierPrice
 *
 * @since 2.0.0
 */
class TierPrice extends \Magento\CatalogImportExport\Model\Import\Product\Validator\AbstractPrice
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\StoreResolver
     * @since 2.0.0
     */
    protected $storeResolver;

    /**
     * @var array
     * @since 2.0.0
     */
    private $_tierPriceColumns = [
        AdvancedPricing::COL_TIER_PRICE_WEBSITE,
        AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP,
        AdvancedPricing::COL_TIER_PRICE_QTY,
        AdvancedPricing::COL_TIER_PRICE,
        AdvancedPricing::COL_TIER_PRICE_TYPE
    ];

    /**
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver
    ) {
        $this->storeResolver = $storeResolver;
        parent::__construct($groupRepository, $searchCriteriaBuilder);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function init($context)
    {
        foreach ($this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems() as $group) {
            $this->customerGroups[$group->getCode()] = $group->getId();
        }
        $this->context = $context;
    }

    /**
     * @param string $attribute
     * @return void
     * @since 2.0.0
     */
    protected function addDecimalError($attribute)
    {
        $this->_addMessages(
            [
                sprintf(
                    $this->context->retrieveMessageTemplate(
                        RowValidatorInterface::ERROR_INVALID_ATTRIBUTE_DECIMAL
                    ),
                    $attribute
                )
            ]
        );
    }

    /**
     * Get existing customers groups
     *
     * @return array
     * @since 2.0.0
     */
    public function getCustomerGroups()
    {
        if (!$this->customerGroups) {
            $this->init($this->context);
        }
        return $this->customerGroups;
    }

    /**
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     * Validation
     *
     * @param mixed $value
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        if (!$this->customerGroups) {
            $this->init($this->context);
        }
        $valid = true;
        if ($this->isValidValueAndLength($value)) {
            if (!isset($value[AdvancedPricing::COL_TIER_PRICE_WEBSITE])
                || !isset($value[AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP])
                || !isset($value[AdvancedPricing::COL_TIER_PRICE_QTY])
                || !isset($value[AdvancedPricing::COL_TIER_PRICE])
                || !isset($value[AdvancedPricing::COL_TIER_PRICE_TYPE])
                || $this->hasEmptyColumns($value)
            ) {
                $this->_addMessages([self::ERROR_TIER_DATA_INCOMPLETE]);
                $valid = false;
            } elseif ($value[AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP] != AdvancedPricing::VALUE_ALL_GROUPS
                && !isset($this->customerGroups[$value[AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP]])
            ) {
                $this->_addMessages([self::ERROR_INVALID_TIER_PRICE_GROUP]);
                $valid = false;
            }
            if ($valid) {
                if (!is_numeric($value[AdvancedPricing::COL_TIER_PRICE_QTY])
                    || $value[AdvancedPricing::COL_TIER_PRICE_QTY] < 0) {
                    $this->addDecimalError(AdvancedPricing::COL_TIER_PRICE_QTY);
                    $valid = false;
                }
                if (!is_numeric($value[AdvancedPricing::COL_TIER_PRICE])
                    || $value[AdvancedPricing::COL_TIER_PRICE] < 0) {
                    $this->addDecimalError(AdvancedPricing::COL_TIER_PRICE);
                    $valid = false;
                }
            }
        }
        return $valid;
    }

    /**
     * Check if at list one value and length are valid
     *
     * @param array $value
     * @return bool
     * @since 2.0.0
     */
    protected function isValidValueAndLength(array $value)
    {
        $isValid = false;
        foreach ($this->_tierPriceColumns as $column) {
            if (isset($value[$column]) && strlen($value[$column])) {
                $isValid = true;
            }
        }
        return $isValid;
    }

    /**
     * Check if value has empty columns
     *
     * @param array $value
     * @return bool
     * @since 2.0.0
     */
    protected function hasEmptyColumns(array $value)
    {
        $hasEmptyValues = false;
        foreach ($this->_tierPriceColumns as $column) {
            if (!strlen($value[$column])) {
                $hasEmptyValues = true;
            }
        }
        return $hasEmptyValues;
    }
}
