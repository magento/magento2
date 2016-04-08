<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Method\Checks;

use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Model\Checks\SpecificationInterface;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Billing\AgreementFactory;
use Magento\Quote\Model\Quote;

class SpecificationPlugin
{
    /**
     * @var AgreementFactory
     */
    protected $_agreementFactory;

    /**
     * @param AgreementFactory $agreementFactory
     */
    public function __construct(AgreementFactory $agreementFactory)
    {
        $this->_agreementFactory = $agreementFactory;
    }

    /**
     * Override check for Billing Agreements
     *
     * @param SpecificationInterface $specification
     * @param \Closure $proceed
     * @param MethodInterface $paymentMethod
     * @param Quote $quote
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsApplicable(
        SpecificationInterface $specification,
        \Closure $proceed,
        MethodInterface $paymentMethod,
        Quote $quote
    ) {
        $originallyIsApplicable = $proceed($paymentMethod, $quote);
        if (!$originallyIsApplicable) {
            return false;
        }

        if ($paymentMethod->getCode() == Config::METHOD_BILLING_AGREEMENT) {
            if ($quote->getCustomerId()) {
                $availableBA = $this->_agreementFactory->create()->getAvailableCustomerBillingAgreements(
                    $quote->getCustomerId()
                );
                return count($availableBA) > 0;
            }
            return false;
        }

        return true;
    }
}
