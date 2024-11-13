<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Extension point for setting shipping methods for a specified shopping cart
 * All objects that are responsible for setting shipping methods on a cart via GraphQl
 * should implement this interface.
 *
 * @api
 */
interface SetShippingMethodsOnCartInterface
{
    /**
     * Set shipping methods for a specified shopping cart
     *
     * @param ContextInterface $context
     * @param CartInterface $cart
     * @param array $shippingMethodsInput
     * @return void
     * @throws GraphQlInputException
     * @throws GraphQlAuthorizationException
     * @throws GraphQlAuthenticationException
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(ContextInterface $context, CartInterface $cart, array $shippingMethodsInput): void;
}
