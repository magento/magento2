<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogCustomerGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\CatalogCustomerGraphQl\Model\Resolver\Product\Price\Tiers;
use Magento\CatalogCustomerGraphQl\Model\Resolver\Product\Price\TiersFactory;
use Magento\CatalogCustomerGraphQl\Model\Resolver\Customer\GetCustomerGroup;
use Magento\Store\Api\Data\StoreInterface;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderPool as PriceProviderPool;
use Magento\Catalog\Api\Data\ProductTierPriceInterface;

/**
 * Resolver for price_tiers
 */
class PriceTiers implements ResolverInterface
{
    /**
     * @var TiersFactory
     */
    private $tiersFactory;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var GetCustomerGroup
     */
    private $getCustomerGroup;

    /**
     * @var int
     */
    private $customerGroupId;

    /**
     * @var Tiers
     */
    private $tiers;

    /**
     * @var Discount
     */
    private $discount;

    /**
     * @var PriceProviderPool
     */
    private $priceProviderPool;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param ValueFactory $valueFactory
     * @param TiersFactory $tiersFactory
     * @param GetCustomerGroup $getCustomerGroup
     * @param Discount $discount
     * @param PriceProviderPool $priceProviderPool
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        ValueFactory $valueFactory,
        TiersFactory $tiersFactory,
        GetCustomerGroup $getCustomerGroup,
        Discount $discount,
        PriceProviderPool $priceProviderPool,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->valueFactory = $valueFactory;
        $this->tiersFactory = $tiersFactory;
        $this->getCustomerGroup = $getCustomerGroup;
        $this->discount = $discount;
        $this->priceProviderPool = $priceProviderPool;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @inheritdoc
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

        if (empty($this->tiers)) {
            $this->customerGroupId = $this->getCustomerGroup->execute($context->getUserId());
            $this->tiers = $this->tiersFactory->create(['customerGroupId' => $this->customerGroupId]);
        }

        $product = $value['model'];
        $productId = $product->getId();
        $this->tiers->addProductFilter($productId);

        return $this->valueFactory->create(
            function () use ($productId, $context) {
                /** @var StoreInterface $store */
                $store = $context->getExtensionAttributes()->getStore();

                $productPrice = $this->tiers->getProductRegularPrice($productId) ?? 0.0;
                $tierPrices = $this->tiers->getProductTierPrices($productId) ?? [];

                return $this->formatProductTierPrices($tierPrices, $productPrice, $store);
            }
        );
    }

    /**
     * Format tier prices for output
     *
     * @param ProductTierPriceInterface[] $tierPrices
     * @param float $productPrice
     * @param StoreInterface $store
     * @return array
     */
    private function formatProductTierPrices(array $tierPrices, float $productPrice, StoreInterface $store): array
    {
        $tiers = [];

        foreach ($tierPrices as $tierPrice) {
            $tierPrice->setValue($this->priceCurrency->convertAndRound($tierPrice->getValue()));
            $percentValue = $tierPrice->getExtensionAttributes()->getPercentageValue();
            if ($percentValue && is_numeric($percentValue)) {
                $discount = $this->discount->getDiscountByPercent($productPrice, (float)$percentValue);
            } else {
                $discount = $this->discount->getDiscountByDifference($productPrice, (float)$tierPrice->getValue());
            }

            $tiers[] = [
                "discount" => $discount,
                "quantity" => $tierPrice->getQty(),
                "final_price" => [
                    "value" => $tierPrice->getValue(),
                    "currency" => $store->getCurrentCurrencyCode()
                ]
            ];
        }
        return $tiers;
    }
}
