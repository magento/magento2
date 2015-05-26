<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing;

class TierPrice extends \Magento\CatalogImportExport\Model\Import\Product\Validator\AbstractPrice
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\StoreResolver
     */
    protected $storeResolver;

    /**
     * @var array
     */
    private $_tierPriceColumns = [
        AdvancedPricing::COL_TIER_PRICE_WEBSITE,
        AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP,
        AdvancedPricing::COL_TIER_PRICE_QTY,
        AdvancedPricing::COL_TIER_PRICE
    ];

    /**
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver
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
     * Call parent init()
     *
     * @return $this
     */
    public function init()
    {
        return parent::init();
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        if ($this->_isValidValueAndLength($value)) {
            if (!isset($value[AdvancedPricing::COL_TIER_PRICE_WEBSITE])
                || !isset($value[AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP])
                || !isset($value[AdvancedPricing::COL_TIER_PRICE_QTY])
                || !isset($value[AdvancedPricing::COL_TIER_PRICE])
                || $this->_hasEmptyColumns($value)
            ) {
                $this->_addMessages([self::ERROR_TIER_DATA_INCOMPLETE]);
                return false;
            } elseif ($value[AdvancedPricing::COL_TIER_PRICE_WEBSITE] != self::VALUE_ALL
                && !$this->storeResolver->getWebsiteCodeToId($value[AdvancedPricing::COL_TIER_PRICE_WEBSITE])
            ) {
                $this->_addMessages([self::ERROR_INVALID_TIER_PRICE_SITE]);
                return false;
            } elseif ($value[AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP] != self::VALUE_ALL && !isset(
                    $this->customerGroups[$value[AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP]]
                )
            ) {
                $this->_addMessages([self::ERROR_INVALID_TIER_PRICE_GROUP]);
                return false;
            } elseif ($value[AdvancedPricing::COL_TIER_PRICE_QTY] <= 0 || $value['tier_price'] <= 0) {
                $this->_addMessages([self::ERROR_INVALID_TIER_PRICE_QTY]);
                return false;
            }
        }
        return true;
    }

    /**
     * Check if at list one value and length are valid
     *
     * @param array $value
     * @return bool
     */
    protected function _isValidValueAndLength(array $value)
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
     */
    protected function _hasEmptyColumns(array $value)
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