<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceInfo\Factory as PriceInfoFactory;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\GraphQlCatalog\Model\Type\Handler\PriceAdjustment;

/**
 * Format a product's price information to conform to GraphQL schema representation
 */
class Price
{
    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var PriceInfoFactory */
    private $priceInfoFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param PriceInfoFactory $priceInfoFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        PriceInfoFactory $priceInfoFactory
    ) {
        $this->storeManager = $storeManager;
        $this->priceInfoFactory = $priceInfoFactory;
    }

    /**
     * Format product's tier price data to conform to GraphQL schema
     *
     * @param Product $product
     * @param array $productData
     * @return array
     */
    public function format(Product $product, array $productData)
    {
        $priceInfo = $this->priceInfoFactory->create($product);
        /** @var \Magento\Catalog\Pricing\Price\FinalPriceInterface $finalPrice */
        $finalPrice = $priceInfo->getPrice('final_price');
        $minimalPriceAmount =  $finalPrice->getMinimalPrice();
        $maximalPriceAmount =  $finalPrice->getMaximalPrice();
        $regularPriceAmount =  $priceInfo->getPrice('regular_price')->getAmount();

        $productData['price'] = [
            'minimalPrice' => [
                'amount' => [
                    'value' => $minimalPriceAmount->getValue(),
                    'currency' => $this->getStoreCurrencyCode()
                ],
                'adjustments' => $this->createAdjustmentsArray($priceInfo->getAdjustments(), $minimalPriceAmount)
            ],
            'regularPrice' => [
                'amount' => [
                    'value' => $regularPriceAmount->getValue(),
                    'currency' => $this->getStoreCurrencyCode()
                ],
                'adjustments' => $this->createAdjustmentsArray($priceInfo->getAdjustments(), $regularPriceAmount)
            ],
            'maximalPrice' => [
                'amount' => [
                    'value' => $maximalPriceAmount->getValue(),
                    'currency' => $this->getStoreCurrencyCode()
                ],
                'adjustments' => $this->createAdjustmentsArray($priceInfo->getAdjustments(), $maximalPriceAmount)
            ]
        ];

        return $productData;
    }

    /**
     * Fill an adjustment array structure with amounts from an amount type
     *
     * @param AdjustmentInterface[] $adjustments
     * @param AmountInterface $amount
     * @return array
     */
    private function createAdjustmentsArray(array $adjustments, AmountInterface $amount)
    {
        $priceAdjustmentsArray = [];
        foreach ($adjustments as $adjustmentCode => $adjustment) {
            if ($amount->hasAdjustment($adjustmentCode) && $amount->getAdjustmentAmount($adjustmentCode)) {
                $priceAdjustmentsArray[] = [
                    'code' => $adjustmentCode,
                    'amount' => [
                        'value' => $amount->getAdjustmentAmount($adjustmentCode),
                        'currency' => $this->getStoreCurrencyCode(),
                    ],
                    'description' => $adjustment->isIncludedInDisplayPrice() ?
                        PriceAdjustment::ADJUSTMENT_INCLUDED : PriceAdjustment::ADJUSTMENT_EXCLUDED
                ];
            }
        }
        return $priceAdjustmentsArray;
    }

    /**
     * Retrieve current store's currency code
     *
     * @return string
     */
    private function getStoreCurrencyCode()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();
        return $store->getCurrentCurrencyCode();
    }
}
