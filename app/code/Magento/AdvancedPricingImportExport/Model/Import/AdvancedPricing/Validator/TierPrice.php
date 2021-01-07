<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product\StoreResolver;
use Magento\CatalogImportExport\Model\Import\Product\Validator\AbstractImportValidator;
use Magento\CatalogImportExport\Model\Import\Product\Validator\AbstractPrice;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;

class TierPrice extends AbstractPrice
{
    /**
     * @var StoreResolver
     */
    protected $storeResolver;

    /**
     * @var array
     */
    private $_tierPriceColumns = [
        AdvancedPricing::COL_TIER_PRICE_WEBSITE,
        AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP,
        AdvancedPricing::COL_TIER_PRICE_QTY,
        AdvancedPricing::COL_TIER_PRICE,
        AdvancedPricing::COL_TIER_PRICE_TYPE
    ];

    /**
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreResolver $storeResolver
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreResolver $storeResolver
    ) {
        $this->storeResolver = $storeResolver;
        parent::__construct($groupRepository, $searchCriteriaBuilder);
    }

    /**
     * Initialize method
     *
     * @param Product $context
     *
     * @return RowValidatorInterface|AbstractImportValidator|void
     * @throws LocalizedException
     */
    public function init($context)
    {
        foreach ($this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems() as $group) {
            $this->customerGroups[$group->getCode()] = $group->getId();
        }
        $this->context = $context;
    }

    /**
     * Add decimal error
     *
     * @param string $attribute
     *
     * @return void
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
     */
    public function getCustomerGroups()
    {
        if (!$this->customerGroups) {
            $this->init($this->context);
        }
        return $this->customerGroups;
    }

    /**
     * Validation
     *
     * @param mixed $value
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     *
     * @return bool
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
     *
     * @return bool
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
