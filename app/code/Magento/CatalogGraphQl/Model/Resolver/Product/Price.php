<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceInfo\Factory as PriceInfoFactory;
use Magento\Store\Model\StoreManagerInterface;

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
     * @inheritdoc
     *
     * Format product's tier price data to conform to GraphQL schema
     *
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @throws \Exception
     * @return array
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
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

        return $prices;
    }

    /**
     * Fill a price with an adjustment array structure with amounts from an amount type
     *
     * @param AdjustmentInterface[] $adjustments
     * @param AmountInterface $amount
     * @return array
     */
    private function createAdjustmentsArray(array $adjustments, AmountInterface $amount) : array
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
