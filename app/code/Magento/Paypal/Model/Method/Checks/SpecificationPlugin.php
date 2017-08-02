<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Method\Checks;

use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Model\Checks\SpecificationInterface;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Billing\AgreementFactory;
use Magento\Quote\Model\Quote;

/**
 * Plugin for \Magento\Payment\Model\Checks\Composite
 */
class SpecificationPlugin
{
    /**
     * @var AgreementFactory
     * @since 2.2.0
     */
    private $agreementFactory;

    /**
     * @param AgreementFactory $agreementFactory
     */
    public function __construct(AgreementFactory $agreementFactory)
    {
        $this->agreementFactory = $agreementFactory;
    }

    /**
     * Override check for Billing Agreements
     *
     * @param SpecificationInterface $specification
     * @param bool $result
     * @param MethodInterface $paymentMethod
     * @param Quote $quote
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterIsApplicable(
        SpecificationInterface $specification,
        $result,
        MethodInterface $paymentMethod,
        Quote $quote
    ) {
        if (!$result) {
            return false;
        }

        if ($paymentMethod->getCode() == Config::METHOD_BILLING_AGREEMENT) {
            if ($quote->getCustomerId()) {
                $availableBA = $this->agreementFactory->create()->getAvailableCustomerBillingAgreements(
                    $quote->getCustomerId()
                );

                return count($availableBA) > 0;
            }

            return false;
        }

        return true;
    }
}
