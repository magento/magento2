<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\QuoteGraphQl\Model\Cart\UpdateCartCurrency;
use Magento\Quote\Model\Cart\CustomerCartResolver;

/**
 * Get cart for the customer
 */
class CustomerCart implements ResolverInterface
{
    /**
     * @var CustomerCartResolver
     */
    private $customerCartResolver;

    /**
     * @var UpdateCartCurrency
     */
    private UpdateCartCurrency $updateCartCurrency;

    /**
     * CustomerCart constructor.
     *
     * @param CustomerCartResolver $customerCartResolver
     * @param UpdateCartCurrency $updateCartCurrency
     */
    public function __construct(
        CustomerCartResolver $customerCartResolver,
        UpdateCartCurrency $updateCartCurrency
    ) {
        $this->customerCartResolver = $customerCartResolver;
        $this->updateCartCurrency = $updateCartCurrency;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $currentUserId = $context->getUserId();

        /**
         * @var ContextInterface $context
         */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The request is allowed for logged in customer'));
        }

        try {
            $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
            $cart = $this->updateCartCurrency->execute(
                $this->customerCartResolver->resolve($currentUserId),
                $storeId
            );
        } catch (\Exception $e) {
            $cart = null;
        }

        return [
            'model' => $cart
        ];
    }
}
