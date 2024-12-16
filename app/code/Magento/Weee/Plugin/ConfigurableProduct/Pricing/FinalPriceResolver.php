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
        return $this->weeePriceDisplay()
            ? $product->getPriceInfo()->getPrice(CatalogFinalPrice::PRICE_CODE)->getAmount()->getValue()
            : $product->getPriceInfo()->getPrice(CatalogFinalPrice::PRICE_CODE)->getValue();
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
