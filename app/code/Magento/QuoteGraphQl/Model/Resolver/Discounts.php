<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\GetDiscounts;
use Magento\Quote\Model\Quote;

/**
 * @inheritdoc
 */
class Discounts implements ResolverInterface
{
    public const TYPE_SHIPPING = "SHIPPING";
    public const TYPE_ITEM = "ITEM";

    /**
     * @param GetDiscounts $getDiscounts
     */
    public function __construct(
        private readonly GetDiscounts $getDiscounts,
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
        /** @var Quote $quote */
        $quote = $value['model'];
        $address = $quote->getIsVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        $discounts = $address->getExtensionAttributes()?->getDiscounts() ?? [];
        return $this->getDiscounts->execute(
            $quote,
            $discounts
        );
    }
}
