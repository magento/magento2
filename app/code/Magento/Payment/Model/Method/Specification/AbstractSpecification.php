<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Method\Specification;

use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Payment\Model\Method\SpecificationInterface;

/**
 * Abstract specification
 *
 * @api
 * @since 2.0.0
 */
abstract class AbstractSpecification implements SpecificationInterface
{
    /**
     * Payment methods info
     *
     * @var array
     * @since 2.0.0
     */
    protected $methodsInfo = [];

    /**
     * Construct
     *
     * @param PaymentConfig $paymentConfig
     * @since 2.0.0
     */
    public function __construct(PaymentConfig $paymentConfig)
    {
        $this->methodsInfo = $paymentConfig->getMethodsInfo();
    }
}
