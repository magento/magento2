<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Plugin\DataProvider\Product;

use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Adjust GraphQL price filter values to match indexed
 * catalog prices when tax display differs from catalog prices.
 */
class SearchCriteriaBuilderPlugin
{
    /**
     * @param TaxConfig $taxConfig
     * @param Calculation $taxCalculation
     * @param GroupManagementInterface $groupManagement
     * @param PriceCurrencyInterface $priceCurrency
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly TaxConfig $taxConfig,
        private readonly Calculation $taxCalculation,
        private readonly GroupManagementInterface $groupManagement,
        private readonly PriceCurrencyInterface $priceCurrency,
        private readonly StoreManagerInterface $storeManager,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Convert GraphQL price filter from display price to indexed price.
     *
     * @param SearchCriteriaBuilder $subject
     * @param array $args
     * @param bool $includeAggregation
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeBuild(SearchCriteriaBuilder $subject, array $args, bool $includeAggregation): array
    {
        if (!isset($args['filter']['price']) || !is_array($args['filter']['price'])) {
            return [$args, $includeAggregation];
        }

        $args['filter']['price'] = $this->adjustPriceFilterForTax($args['filter']['price']);

        return [$args, $includeAggregation];
    }

    /**
     * Adjust GraphQL price filter values
     *
     * @param array $priceFilter
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function adjustPriceFilterForTax(array $priceFilter): array
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        $conversion = $this->taxConfig->needPriceConversion($storeId);

        if (!$conversion) {
            return $priceFilter;
        }

        $customerGroup = $this->groupManagement->getNotLoggedInGroup();
        $customerTaxClassId = (int) $customerGroup->getTaxClassId();

        $request = $this->taxCalculation->getRateRequest(
            null,
            null,
            $customerTaxClassId,
            $storeId
        );
        $productTaxClassId = (int) $this->scopeConfig->getValue(
            TaxHelper::CONFIG_DEFAULT_PRODUCT_TAX_CLASS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($productTaxClassId) {
            $request->setProductClassId($productTaxClassId);
        }

        $rate = (float) $this->taxCalculation->getRate($request);
        if ($rate <= 0.0) {
            return $priceFilter;
        }

        $factor = 1 + ($rate / 100);

        foreach (['from', 'to'] as $key) {
            if (!isset($priceFilter[$key]) || $priceFilter[$key] === '' || $priceFilter[$key] === null) {
                continue;
            }

            $value = (float) $priceFilter[$key];

            if ($conversion === TaxConfig::PRICE_CONVERSION_PLUS) {
                // Catalog prices ex-tax, display incl-tax: widen bounds to match displayed rounding.
                if ($key === 'from') {
                    $value = floor(($value / $factor) * 100) / 100;
                } else {
                    $value = ceil(($value / $factor) * 100) / 100;
                }
            } elseif ($conversion === TaxConfig::PRICE_CONVERSION_MINUS) {
                if ($key === 'from') {
                    $value = ceil($value * $factor * 100) / 100;
                } else {
                    $value = floor($value * $factor * 100) / 100;
                }
            } else {
                continue;
            }

            $priceFilter[$key] = $this->priceCurrency->round($value);
        }

        return $priceFilter;
    }
}
