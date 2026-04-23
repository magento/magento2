<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\WeeeGraphQl\Model\FixedProductTaxes;

use Magento\Sales\Model\Order\Item;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Config;
use Magento\Weee\Helper\Data;

/**
 * FPT data provider for order item
 */
class PricesProvider
{
    /**
     * @param Data $weeHelper
     * @param TaxHelper $taxHelper
     */
    public function __construct(
        private readonly Data $weeHelper,
        private readonly TaxHelper $taxHelper
    ) {
    }

    /**
     * Returns an array of different FPTs applied on the order item
     *
     * @param Item $orderItem
     * @param StoreInterface $store
     * @return array
     */
    public function execute(Item $orderItem, StoreInterface $store): array
    {
        if (!$this->weeHelper->isEnabled($store)) {
            return [];
        }

        $prices = $this->weeHelper->getApplied($orderItem);
        $displayInclTaxes = $this->taxHelper->getPriceDisplayType($store) === Config::DISPLAY_TYPE_INCLUDING_TAX;
        $currency = $orderItem->getOrder()->getOrderCurrencyCode();

        $fixedProductTaxes = [];
        foreach ($prices as $price) {
            $fixedProductTaxes[] = [
                'amount' => [
                    'value' => $displayInclTaxes ? $price['amount_incl_tax'] : $price['amount'],
                    'currency' => $currency,
                ],
                'label' => $price['title']
            ];
        }

        return $fixedProductTaxes;
    }
}
