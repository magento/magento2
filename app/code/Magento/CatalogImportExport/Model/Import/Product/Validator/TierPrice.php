<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;

class TierPrice extends AbstractPrice implements RowValidatorInterface
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\StoreResolver
     */
    protected $storeResolver;

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
     * {@inheritdoc}
     */
    public function init($context)
    {
        return parent::init($context);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        if (isset(
                $value['_tier_price_website']
            ) && strlen(
                $value['_tier_price_website']
            ) || isset(
                $value['_tier_price_customer_group']
            ) && strlen(
                $value['_tier_price_customer_group']
            ) || isset(
                $value['_tier_price_qty']
            ) && strlen(
                $value['_tier_price_qty']
            ) || isset(
                $value['_tier_price_price']
            ) && strlen(
                $value['_tier_price_price']
            )
        ) {
            if (!isset(
                    $value['_tier_price_website']
                ) || !isset(
                    $value['_tier_price_customer_group']
                ) || !isset(
                    $value['_tier_price_qty']
                ) || !isset(
                    $value['_tier_price_price']
                ) || !strlen(
                    $value['_tier_price_website']
                ) || !strlen(
                    $value['_tier_price_customer_group']
                ) || !strlen(
                    $value['_tier_price_qty']
                ) || !strlen(
                    $value['_tier_price_price']
                )
            ) {
                $this->_addMessages([self::ERROR_TIER_DATA_INCOMPLETE]);
                return false;
            } elseif ($value['_tier_price_website'] != self::VALUE_ALL
                && !$this->storeResolver->getWebsiteCodeToId($value['_tier_price_website'])
            ) {
                $this->_addMessages([self::ERROR_INVALID_TIER_PRICE_SITE]);
                return false;
            } elseif ($value['_tier_price_customer_group'] != self::VALUE_ALL && !isset(
                    $this->customerGroups[$value['_tier_price_customer_group']]
                )
            ) {
                $this->_addMessages([self::ERROR_INVALID_TIER_PRICE_GROUP]);
                return false;
            } elseif ($value['_tier_price_qty'] <= 0 || $value['_tier_price_price'] <= 0) {
                $this->_addMessages([self::ERROR_INVALID_TIER_PRICE_QTY]);
                return false;
            }
        }
        return true;
    }
}
