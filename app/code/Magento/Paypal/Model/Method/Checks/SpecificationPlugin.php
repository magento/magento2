<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Model\Method\Checks;

use Magento\Payment\Model\Checks\PaymentMethodChecksInterface;
use Magento\Payment\Model\Checks\SpecificationInterface;
use Magento\Paypal\Model\Billing\AgreementFactory;
use Magento\Sales\Model\Quote;

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
     * @param PaymentMethodChecksInterface $paymentMethod
     * @param Quote $quote
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsApplicable(
        SpecificationInterface $specification,
        \Closure $proceed,
        PaymentMethodChecksInterface $paymentMethod,
        Quote $quote
    ) {
        $originallyIsApplicable = $proceed($paymentMethod, $quote);
        if (!$originallyIsApplicable || $paymentMethod->getCode() != 'paypal_billing_agreement'
            || !$quote->getCustomerId()
        ) {
            return $originallyIsApplicable;
        }
        $availableBA = $this->_agreementFactory->create()->getAvailableCustomerBillingAgreements(
            $quote->getCustomerId()
        );
        return count($availableBA) > 0;
    }
}
