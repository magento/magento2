<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Checks;

use Magento\Sales\Model\Quote;

class Composite implements SpecificationInterface
{
    /** @var SpecificationInterface[]  */
    protected $list = [];

    /**
     * @param SpecificationInterface[] $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
    }

    /**
     * Check whether payment method is applicable to quote
     *
     * @param PaymentMethodChecksInterface $paymentMethod
     * @param \Magento\Sales\Model\Quote $quote
     * @return bool
     */
    public function isApplicable(PaymentMethodChecksInterface $paymentMethod, Quote $quote)
    {
        foreach ($this->list as $specification) {
            if (!$specification->isApplicable($paymentMethod, $quote)) {
                return false;
            }
        }
        return true;
    }
}
