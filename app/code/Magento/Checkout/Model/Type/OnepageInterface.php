<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model\Type;

/**
 * Onepage interface
 *
 * @api
 */
interface OnepageInterface
{
    /**
     * Checkout types: Checkout as Guest, Register, Logged In Customer
     */
    const METHOD_GUEST    = 'guest';
    const METHOD_REGISTER = 'register';
    const METHOD_CUSTOMER = 'customer';
    const USE_FOR_SHIPPING = 1;
    const NOT_USE_FOR_SHIPPING = 0;
}
