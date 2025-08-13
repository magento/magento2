<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\QuoteGraphQl\Model\CartItem\GetItemsData;

/**
 * @inheritdoc
 */
class CartItems implements ResolverInterface
{
    /**
     * @param Uid $uidEncoder
     * @param GetItemsData $getItemsData
     */
    public function __construct(
        private readonly Uid $uidEncoder,
        private readonly GetItemsData $getItemsData
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
        /** @var CartInterface $cart */
        $cart = $value['model'];
        $cartItems = $cart->getAllVisibleItems();

        return $this->getItemsData->execute($cartItems);
    }
}
