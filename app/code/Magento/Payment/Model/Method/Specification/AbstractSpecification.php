<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Payment\Model\Method\Specification;

use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Payment\Model\Method\SpecificationInterface;

/**
 * Abstract specification
 *
 * @api
 * @since 100.0.2
 */
abstract class AbstractSpecification implements SpecificationInterface
{
    /**
     * Payment methods info
     *
     * @var array
     */
    protected $methodsInfo = [];

    /**
     * Construct
     *
     * @param PaymentConfig $paymentConfig
     */
    public function __construct(PaymentConfig $paymentConfig)
    {
        $this->methodsInfo = $paymentConfig->getMethodsInfo();
    }
}
