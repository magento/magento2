<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Model\Payment\Method\Specification;

use Magento\Payment\Model\Method\Specification\AbstractSpecification;

/**
 * Enable method specification
 * @since 2.0.0
 */
class Enabled extends AbstractSpecification
{
    /**
     * Allow multiple address flag
     */
    const FLAG_ALLOW_MULTIPLE_ADDRESS = 'allow_multiple_address';

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isSatisfiedBy($paymentMethod)
    {
        return isset(
            $this->methodsInfo[$paymentMethod][self::FLAG_ALLOW_MULTIPLE_ADDRESS]
        ) && $this->methodsInfo[$paymentMethod][self::FLAG_ALLOW_MULTIPLE_ADDRESS];
    }
}
