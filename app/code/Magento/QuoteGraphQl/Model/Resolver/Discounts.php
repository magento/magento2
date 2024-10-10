<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\GetDiscounts;

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
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $quote = $value['model'];

        return $this->getDiscounts->execute(
            $quote,
            $quote->getShippingAddress()->getExtensionAttributes()->getDiscounts() ?? []
        );
    }
}
