<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing;

class GroupPrice extends \Magento\CatalogImportExport\Model\Import\Product\Validator\AbstractPrice
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\StoreResolver
     */
    protected $storeResolver;

    /**
     * @var array
     */
    private $_groupPriceColumns = [
        AdvancedPricing::COL_GROUP_PRICE_WEBSITE,
        AdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP,
        AdvancedPricing::COL_GROUP_PRICE
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
        foreach ($this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems() as $group) {
            $this->customerGroups[$group->getCode()] = $group->getId();
        }
    }

    /**
     * Validate value
     *
     * @param array $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        if (!$this->customerGroups) {
            $this->init();
        }
        if ($this->isValidValueAndLength($value)) {
            if (!isset($value[AdvancedPricing::COL_GROUP_PRICE_WEBSITE])
                || !isset($value[AdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP])
                || $this->hasEmptyColumns($value)) {
                $this->_addMessages([self::ERROR_GROUP_PRICE_DATA_INCOMPLETE]);
                return false;
            } elseif (
                $value[AdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP] == AdvancedPricing::VALUE_ALL_GROUPS
                || !isset($this->customerGroups[$value[AdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP]])
            ) {
                $this->_addMessages([self::ERROR_INVALID_GROUP_PRICE_GROUP]);
                return false;
            }
        }
        return true;
    }

    /**
     * Get existing customers groups
     *
     * @return array
     */
    public function getCustomerGroups()
    {
        if (!$this->customerGroups) {
            $this->init();
        }
        return $this->customerGroups;
    }

    /**
     * Check if at list one value and length are valid
     *
     * @param array $value
     * @return bool
     */
    protected function isValidValueAndLength(array $value)
    {
        $isValid = false;
        foreach ($this->_groupPriceColumns as $column) {
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
    protected function hasEmptyColumns(array $value)
    {
        $hasEmptyValues = false;
        foreach ($this->_groupPriceColumns as $column) {
            if (!strlen($value[$column])) {
                $hasEmptyValues = true;
            }
        }
        return $hasEmptyValues;
    }
}
