<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogCustomerGraphQl\Model\Resolver;

use Magento\Catalog\Api\Data\ProductTierPriceInterface;
use Magento\CatalogCustomerGraphQl\Model\Resolver\Customer\GetCustomerGroup;
use Magento\CatalogCustomerGraphQl\Model\Resolver\Product\Price\Tiers;
use Magento\CatalogCustomerGraphQl\Model\Resolver\Product\Price\TiersFactory;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderPool as PriceProviderPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Pricing\PriceCurrencyInterface;

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
     * @var array
     */
    private $formatAndFilterTierPrices = [];

    /**
     * @var array
     */
    private $tierPricesQty = [];

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

        if ($product->hasData('can_show_price') && $product->getData('can_show_price') === false) {
            return [];
        }

        if (!$product->getTierPrices()) {
            return [];
        }

        $productId = (int)$product->getId();
        $this->tiers->addProductFilter($productId);

        return $this->valueFactory->create(
            function () use ($productId, $context) {
                $currencyCode = $context->getExtensionAttributes()->getStore()->getCurrentCurrencyCode();

                $productPrice = $this->tiers->getProductRegularPrice($productId) ?? 0.0;
                $tierPrices = $this->tiers->getProductTierPrices($productId) ?? [];
                return $this->formatAndFilterTierPrices($productPrice, $tierPrices, $currencyCode);
            }
        );
    }

    /**
     * Format and filter tier prices for output
     *
     * @param float $productPrice
     * @param ProductTierPriceInterface[] $tierPrices
     * @param string $currencyCode
     * @return array
     */
    private function formatAndFilterTierPrices(
        float $productPrice,
        array $tierPrices,
        string $currencyCode
    ): array {
        $this->formatAndFilterTierPrices = [];
        $this->tierPricesQty = [];
        foreach ($tierPrices as $key => $tierPrice) {
            $tierPrice->setValue($this->priceCurrency->convertAndRound($tierPrice->getValue()));
            $this->formatTierPrices($productPrice, $currencyCode, $tierPrice);
            $this->filterTierPrices($tierPrices, $key, $tierPrice);
        }
        return $this->formatAndFilterTierPrices;
    }

    /**
     * Format tier prices for output
     *
     * @param float $productPrice
     * @param string $currencyCode
     * @param ProductTierPriceInterface $tierPrice
     */
    private function formatTierPrices(float $productPrice, string $currencyCode, $tierPrice)
    {
        $percentValue = $tierPrice->getExtensionAttributes()->getPercentageValue();
        if ($percentValue && is_numeric($percentValue)) {
            $discount = $this->discount->getDiscountByPercent($productPrice, (float)$percentValue);
        } else {
            $discount = $this->discount->getDiscountByDifference($productPrice, (float)$tierPrice->getValue());
        }

        $this->formatAndFilterTierPrices[] = [
            "discount" => $discount,
            "quantity" => $tierPrice->getQty(),
            "final_price" => [
                "value" => $tierPrice->getValue(),
                "currency" => $currencyCode
            ]
        ];
    }

    /**
     * Filter the lowest price for each quantity
     *
     * @param array $tierPrices
     * @param int $key
     * @param ProductTierPriceInterface $tierPriceItem
     */
    private function filterTierPrices(
        array $tierPrices,
        int $key,
        ProductTierPriceInterface $tierPriceItem
    ) {
        $qty = $tierPriceItem->getQty();
        if (isset($this->tierPricesQty[$qty])) {
            $priceQty = $this->tierPricesQty[$qty];
            if ((float)$tierPriceItem->getValue() < (float)$tierPrices[$priceQty]->getValue()) {
                unset($this->formatAndFilterTierPrices[$priceQty]);
                $this->tierPricesQty[$priceQty] = $key;
            } else {
                unset($this->formatAndFilterTierPrices[$key]);
            }
        } else {
            $this->tierPricesQty[$qty] = $key;
        }
    }
}
