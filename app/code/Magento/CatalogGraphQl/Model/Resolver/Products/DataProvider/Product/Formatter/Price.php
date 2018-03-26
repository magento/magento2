<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceInfo\Factory as PriceInfoFactory;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Catalog\Pricing\Price\FinalPrice;

/**
 * Format a product's price information to conform to GraphQL schema representation
 */
class Price implements FormatterInterface
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
     * {@inheritdoc}
     */
    public function format(Product $product, array $productData = [])
    {
        $priceInfo = $this->priceInfoFactory->create($product);
        /** @var \Magento\Catalog\Pricing\Price\FinalPriceInterface $finalPrice */
        $finalPrice = $priceInfo->getPrice(FinalPrice::PRICE_CODE);
        $minimalPriceAmount =  $finalPrice->getMinimalPrice();
        $maximalPriceAmount =  $finalPrice->getMaximalPrice();
        $regularPriceAmount =  $priceInfo->getPrice(RegularPrice::PRICE_CODE)->getAmount();

        $productData['price'] = [
            'minimalPrice' => $this->createAdjustmentsArray($priceInfo->getAdjustments(), $minimalPriceAmount),
            'regularPrice' => $this->createAdjustmentsArray($priceInfo->getAdjustments(), $regularPriceAmount),
            'maximalPrice' => $this->createAdjustmentsArray($priceInfo->getAdjustments(), $maximalPriceAmount)
        ];

        return $productData;
    }

    /**
     * Fill a price with an adjustment array structure with amounts from an amount type
     *
     * @param AdjustmentInterface[] $adjustments
     * @param AmountInterface $amount
     * @return array
     */
    private function createAdjustmentsArray(array $adjustments, AmountInterface $amount)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();

        $priceArray = [
                'amount' => [
                    'value' => $amount->getValue(),
                    'currency' => $store->getCurrentCurrencyCode()
                ],
                'adjustments' => []
            ];
        $priceAdjustmentsArray = [];
        foreach ($adjustments as $adjustmentCode => $adjustment) {
            if ($amount->hasAdjustment($adjustmentCode) && $amount->getAdjustmentAmount($adjustmentCode)) {
                $priceAdjustmentsArray[] = [
                    'code' => strtoupper($adjustmentCode),
                    'amount' => [
                        'value' => $amount->getAdjustmentAmount($adjustmentCode),
                        'currency' => $store->getCurrentCurrencyCode(),
                    ],
                    'description' => $adjustment->isIncludedInDisplayPrice() ?
                        'INCLUDED' : 'EXCLUDED'
                ];
            }
        }
        $priceArray['adjustments'] = $priceAdjustmentsArray;
        return $priceArray;
    }
}
