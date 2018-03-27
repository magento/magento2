<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\Framework\GraphQl\Resolver\Value;
use Magento\Framework\GraphQl\Resolver\ValueFactory;
use Magento\Framework\Pricing\PriceInfo\Factory as PriceInfoFactory;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Catalog\Pricing\Price\FinalPrice;

/**
 * Format a product's price information to conform to GraphQL schema representation
 */
class Price implements ResolverInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PriceInfoFactory
     */
    private $priceInfoFactory;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param PriceInfoFactory $priceInfoFactory
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        PriceInfoFactory $priceInfoFactory,
        ValueFactory $valueFactory
    ) {
        $this->storeManager = $storeManager;
        $this->priceInfoFactory = $priceInfoFactory;
        $this->valueFactory = $valueFactory;
    }

    /**
     * Format product's tier price data to conform to GraphQL schema
     *
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        array $value = null,
        array $args = null,
        $context,
        ResolveInfo $info
    ): ?Value {
        if (!isset($value['model'])) {
            return null;
        }

        /** @var Product $product */
        $product = $value['model'];
        $product->unsetData('minimal_price');
        $priceInfo = $this->priceInfoFactory->create($product);
        /** @var \Magento\Catalog\Pricing\Price\FinalPriceInterface $finalPrice */
        $finalPrice = $priceInfo->getPrice(FinalPrice::PRICE_CODE);
        $minimalPriceAmount =  $finalPrice->getMinimalPrice();
        $maximalPriceAmount =  $finalPrice->getMaximalPrice();
        $regularPriceAmount =  $priceInfo->getPrice(RegularPrice::PRICE_CODE)->getAmount();

        $prices = [
            'minimalPrice' => $this->createAdjustmentsArray($priceInfo->getAdjustments(), $minimalPriceAmount),
            'regularPrice' => $this->createAdjustmentsArray($priceInfo->getAdjustments(), $regularPriceAmount),
            'maximalPrice' => $this->createAdjustmentsArray($priceInfo->getAdjustments(), $maximalPriceAmount)
        ];

        $result = function () use ($prices) {
            return $prices;
        };

        return $this->valueFactory->create($result);
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
