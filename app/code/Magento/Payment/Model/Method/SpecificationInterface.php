<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Method;

/**
 * Interface SpecificationInterface
 *
 * @api
 * @since 2.0.0
 */
interface SpecificationInterface
{
    /**
     * Check specification is satisfied by payment method
     *
     * @param string $paymentMethod
     * @return bool
     * @since 2.0.0
     */
    public function isSatisfiedBy($paymentMethod);
}
