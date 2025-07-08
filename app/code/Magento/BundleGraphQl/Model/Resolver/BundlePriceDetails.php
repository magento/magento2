<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Resolver;

use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;

class BundlePriceDetails implements ResolverInterface
{
    /**
     * BundlePriceDetails Constructor
     *
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Product $product */
        $product = $value['model'];
        $price = $product->getPrice();
        $finalPrice = $product->getFinalPrice();
        $discountPercentage = ($price) ? (100 - (($finalPrice * 100) / $price)) : 0;
        if ((int)$product->getPriceType() === Price::PRICE_TYPE_DYNAMIC && isset($value['cart_item'])) {
            $discountPercentage = $this->getDiscountPercentageForBundleProduct($value['cart_item']);
        }
        return [
            'main_price' =>  $price,
            'main_final_price' => $finalPrice,
            'discount_percentage' => $discountPercentage
        ];
    }

    /**
     * Calculate discount percentage for bundle product with dynamic pricing enabled
     *
     * @param CartItemInterface $cartItem
     * @return float
     * @throws NoSuchEntityException
     */
    private function getDiscountPercentageForBundleProduct(CartItemInterface $cartItem): float
    {
        if (empty($cartItem->getAppliedRuleIds())) {
            return 0;
        }
        $itemAmount = 0;
        $discountAmount = 0;
        $cart = $this->cartRepository->get($cartItem->getQuoteId());
        foreach ($cart->getAllItems() as $item) {
            if ($item->getParentItemId() == $cartItem->getId()) {
                $itemAmount += $item->getPrice();
                $discountAmount += $item->getDiscountAmount();
            }
        }
        if ($itemAmount && $discountAmount) {
            return ($discountAmount / $itemAmount) * 100;
        }

        return 0;
    }
}
