<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
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
     * CustomerCart constructor.
     *
     * @param CustomerCartResolver $customerCartResolver
     */
    public function __construct(
        CustomerCartResolver $customerCartResolver
    ) {
        $this->customerCartResolver = $customerCartResolver;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $currentUserId = $context->getUserId();

        /**
         * @var ContextInterface $context
         */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The request is allowed for logged in customer'));
        }

        try {
            $cart = $this->customerCartResolver->resolve($currentUserId);
        } catch (\Exception $e) {
            $cart = null;
        }

        return [
            'model' => $cart
        ];
    }
}
