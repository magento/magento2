<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Weee\Plugin\ConfigurableProduct\Pricing;

use Magento\Catalog\Pricing\Price\FinalPrice as CatalogFinalPrice;
use Magento\ConfigurableProduct\Pricing\Price\FinalPriceResolver as ConfigurableProductFinalPriceResolver;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Tax\Pricing\Adjustment;
use Magento\Weee\Helper\Data as WeeeHelperData;

class FinalPriceResolver
{
    /**
     * @var WeeeHelperData
     */
    public WeeeHelperData $weeeHelperData;

    /**
     * @param WeeeHelperData $weeeHelperData
     */
    public function __construct(
        WeeeHelperData $weeeHelperData,
    ) {
        $this->weeeHelperData = $weeeHelperData;
    }

    /**
     * Display price with weee attribute included
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param ConfigurableProductFinalPriceResolver $subject
     * @param float $result
     * @param SaleableInterface $product
     * @return float
     */
    public function afterResolvePrice(
        ConfigurableProductFinalPriceResolver $subject,
        float $result,
        SaleableInterface $product
    ):float {
        $priceInfo = $product->getPriceInfo();
        if (!$this->weeePriceDisplay()) {
            return (float)$priceInfo->getPrice(CatalogFinalPrice::PRICE_CODE)->getValue();
        }

        // The configurable parent applies its adjustments to the resolved price as a base price.
        // Keep the tax in it when the tax adjustment is included in the base price, as the
        // calculator will then extract it; leave it out otherwise, so it is not added twice.
        $amount = $priceInfo->getPrice(CatalogFinalPrice::PRICE_CODE)->getAmount();
        $taxAdjustment = $priceInfo->getAdjustments()[Adjustment::ADJUSTMENT_CODE] ?? null;

        return $taxAdjustment !== null && $taxAdjustment->isIncludedInBasePrice()
            ? (float)$amount->getValue()
            : (float)$amount->getValue(Adjustment::ADJUSTMENT_CODE);
    }

    /**
     * Weee including price display
     *
     * @return bool
     */
    private function weeePriceDisplay():bool
    {
        return $this->weeeHelperData->isDisplayIncl() || $this->weeeHelperData->isDisplayInclDesc();
    }
}
