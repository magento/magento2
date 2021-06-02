<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Model\ResourceModel\Catalog\Frontend\ProductList\Price;

use Magento\Catalog\Model\ResourceModel\Frontend\ProductList\Price;
use Magento\Catalog\Model\ResourceModel\Frontend\ProductList\Price\ExpressionBuilderInterface;
use Magento\Framework\Math\FloatComparator;
use Magento\Tax\Model\Config as TaxConfig;

class TaxExpressionModifier implements Price\ExpressionModifierInterface
{
    const SORT_ORDER = 1000000000;

    /**
     * @var FloatComparator
     */
    private $floatComparator;

    /**
     * @var \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory
     */
    private $taxClassCollectionFactory;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    private $taxCalculation;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    private $taxHelper;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var float[][]
     */
    private $storeProductTaxClassRates = [];

    /**
     * @param FloatComparator $floatComparator
     * @param \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $taxClassCollectionFactory
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     */
    public function __construct(
        FloatComparator $floatComparator,
        \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $taxClassCollectionFactory,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory
    ) {
        $this->floatComparator = $floatComparator;
        $this->taxClassCollectionFactory = $taxClassCollectionFactory;
        $this->taxCalculation = $taxCalculation;
        $this->taxHelper = $taxHelper;
        $this->customerSessionFactory = $customerSessionFactory;
    }

    public function isApplicableTo(string $contextClass, ?string $contextKey, int $storeId): bool
    {
        return ($storeId > 0);
    }

    /**
     * @param int $storeId
     * @param bool $isPricesInclTax
     * @param bool $isDisplayInclTax
     * @param bool $isCrossBorderTradeEnabled
     * @return float[]
     */
    private function getStoreProductTaxClassRates(
        int $storeId,
        bool $isPricesInclTax,
        bool $isDisplayInclTax,
        bool $isCrossBorderTradeEnabled
    ): array {
        $customerSession = $this->customerSessionFactory->create();
        $customerId = $customerSession->getCustomerId();

        $cacheKey = sprintf(
            '%d-%d-%d-%d-%d',
            $storeId,
            $isPricesInclTax,
            $isDisplayInclTax,
            $isCrossBorderTradeEnabled,
            $customerId
        );

        if (!isset($this->storeProductTaxClassRates[$cacheKey])) {
            $this->storeProductTaxClassRates[$cacheKey] = [];

            $customerTaxClassId = null;

            if ($customerId) {
                $customerTaxClassId = $customerSession->getCustomer()->getTaxClassId();
            }

            $rateRequest = $this->taxCalculation->getRateRequest(
                null,
                null,
                $customerTaxClassId,
                $storeId,
                $customerId
            );

            $productTaxClassIds = $this->taxClassCollectionFactory
                ->create()
                ->setClassTypeFilter(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT)
                ->getAllIds();

            foreach ($productTaxClassIds as $productTaxClassId) {
                $rateRequest->setProductClassId($productTaxClassId);

                $rate = !$isCrossBorderTradeEnabled && !$isDisplayInclTax
                    ? 0
                    : $this->taxCalculation->getRate($rateRequest);

                $storeRate = $isCrossBorderTradeEnabled
                    ? 0
                    : $this->taxCalculation->getStoreRate($rateRequest, $storeId);

                /**
                 * +────────────+────────────+─────────────────────+──────────────────────────────────────────+
                 * | Prices     | Display    | Cross-border trade  | Behavior                                 |
                 * +────────────+────────────+─────────────────────+──────────────────────────────────────────+
                 * | Excl. tax  | Excl. tax  | -                   | -                                        |
                 * | Excl. tax  | Incl. tax  | -                   | Apply customer rate                      |
                 * | Incl. tax  | Excl. tax  | No                  | Unapply store rate                       |
                 * | Incl. tax  | Excl. tax  | Yes                 | Unapply customer rate                    |
                 * | Incl. tax  | Incl. tax  | No                  | Unapply store rate, apply customer rate  |
                 * | Incl. tax  | Incl. tax  | Yes                 | -                                        |
                 * +────────────+────────────+─────────────────────+──────────────────────────────────────────+
                 */

                if ($isPricesInclTax) {
                    if ($isCrossBorderTradeEnabled) {
                        // Prices incl. tax / Display excl. tax / with CBT
                        $productRate = 1 - $this->taxCalculation->calcTaxAmount(1, $rate, true, false);
                    } elseif (!$isDisplayInclTax) {
                        // Prices incl. tax / Display excl. tax / without CBT
                        $productRate = 1 - $this->taxCalculation->calcTaxAmount(1, $storeRate, true, false);
                    } else {
                        // Prices incl. tax / Display incl. tax / without CBT
                        $productRate = 1 - $this->taxCalculation->calcTaxAmount(1, $storeRate, true, false);

                        $productRate += $this->taxCalculation->calcTaxAmount(
                            $productRate,
                            $rate,
                            false,
                            false
                        );
                    }
                } else {
                    // Prices excl. tax / Display incl. tax / CBT irrelevant
                    $productRate = 1 + $this->taxCalculation->calcTaxAmount(1, $rate, false, false);
                }

                $productRate = round($productRate, 4);

                if (!$this->floatComparator->equal(1.0, $productRate)) {
                    $this->storeProductTaxClassRates[$cacheKey][$productTaxClassId] = $productRate;
                }
            }
        }

        return $this->storeProductTaxClassRates[$cacheKey];
    }

    public function getPriceExpression(
        $priceExpression,
        $basePriceExpression,
        int $storeId,
        ExpressionBuilderInterface $expressionBuilder
    ) {
        $isPricesInclTax = $this->taxHelper->priceIncludesTax($storeId);
        $isDisplayInclTax = $this->taxHelper->getPriceDisplayType($storeId) !== TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX;
        $isCrossBorderTradeEnabled = $this->taxHelper->isCrossBorderTradeEnabled($storeId);

        /**
         * Only adapt prices when indexed prices need to be converted *and* this is reflected on listing prices.
         * For example, at the time of this writing, @see \Magento\Tax\Pricing\Adjustment::applyAdjustment()
         * decides whether to convert prices based on @see \Magento\Tax\Helper\Data::needPriceConversion(),
         * which does not account for the cross-border trade flag.
         */

        if (
            !$this->taxHelper->needPriceConversion($storeId)
            || ($isPricesInclTax && $isDisplayInclTax && $isCrossBorderTradeEnabled)
            || (!$isPricesInclTax && !$isDisplayInclTax)
        ) {
            return null;
        }

        $productTaxClassRates = $this->getStoreProductTaxClassRates(
            $storeId,
            $isPricesInclTax,
            $isDisplayInclTax,
            $isCrossBorderTradeEnabled
        );

        if (empty($productTaxClassRates)) {
            return null;
        }

        $productTaxRateExpressions = [];

        foreach ($productTaxClassRates as $productTaxClassId => $rate) {
            $productTaxRateExpressions[$productTaxClassId] = $expressionBuilder->double($rate);
        }

        $productTaxRateExpression = $expressionBuilder->caseOf(
            $expressionBuilder->attributeValue('tax_class_id'),
            $productTaxRateExpressions,
            $expressionBuilder->double(1.0)
        );

        $priceExpression = $expressionBuilder->multiply($priceExpression, $productTaxRateExpression);

        return $expressionBuilder->round($priceExpression, 2);
    }

    public function getSortOrder(): int
    {
        return static::SORT_ORDER;
    }
}
