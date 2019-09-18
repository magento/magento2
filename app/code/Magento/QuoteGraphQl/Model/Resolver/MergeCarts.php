<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCart;
use Magento\QuoteGraphQl\Model\Cart\MergeCarts as MergeCartsModel;

/**
 * @inheritdoc
 */
class MergeCarts implements ResolverInterface
{
    /**
     * @var GetCart
     */
    private $getCart;

    /**
     * @var MergeCartsModel
     */
    private $mergeCarts;

    /**
     * @param GetCart $getCart
     * @param MergeCartsModel $mergeCarts
     */
    public function __construct(
        GetCart $getCart,
        MergeCartsModel $mergeCarts
    ) {
        $this->getCart = $getCart;
        $this->mergeCarts = $mergeCarts;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input']['first_cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "first_cart_id" is missing'));
        }
        if (empty($args['input']['second_cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "second_cart_id" is missing'));
        }

        $currentUserId = $context->getUserId();
        $storeId = $storeId = (int) $context->getExtensionAttributes()->getStore()->getId();
        $firstCart = $this->getCart->execute($args['input']['first_cart_id'], $currentUserId, $storeId);
        $secondCart = $this->getCart->execute($args['input']['second_cart_id'], $currentUserId, $storeId);

        $maskedQuoteId = $this->mergeCarts->execute($firstCart, $secondCart);

        return $maskedQuoteId;
    }
}
