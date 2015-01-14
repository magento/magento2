<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Onepage;

use Magento\Sales\Model\Quote\Address;

/**
 * One page checkout status
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Progress extends \Magento\Checkout\Block\Onepage\AbstractOnepage
{
    /**
     * @return Address
     */
    public function getBilling()
    {
        return $this->getQuote()->getBillingAddress();
    }

    /**
     * @return Address
     */
    public function getShipping()
    {
        return $this->getQuote()->getShippingAddress();
    }

    /**
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->getQuote()->getShippingAddress()->getShippingMethod();
    }

    /**
     * @return string
     */
    public function getShippingDescription()
    {
        return $this->getQuote()->getShippingAddress()->getShippingDescription();
    }

    /**
     * @return float
     */
    public function getShippingAmount()
    {
        return $this->getQuote()->getShippingAddress()->getShippingAmount();
    }

    /**
     * @return string
     */
    public function getPaymentHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    /**
     * Get is step completed. if is set 'toStep' then all steps after him is not completed.
     *
     * @param string $currentStep
     * @return bool
     *
     *  @see: \Magento\Checkout\Block\Onepage\AbstractOnepage::_getStepCodes() for allowed values
     */
    public function isStepComplete($currentStep)
    {
        $stepsRevertIndex = array_flip($this->_getStepCodes());

        $toStep = $this->getNextStep();

        if (empty($toStep) || !isset($stepsRevertIndex[$currentStep])) {
            return $this->getCheckout()->getStepData($currentStep, 'complete');
        }

        if ($stepsRevertIndex[$currentStep] > $stepsRevertIndex[$toStep]) {
            return false;
        }

        return $this->getCheckout()->getStepData($currentStep, 'complete');
    }

    /**
     * Return selected shipping rate
     *
     * @return false|Address\Rate
     */
    public function getShippingRate()
    {
        $address = $this->getQuote()->getShippingAddress();
        $rate = $address->getShippingRateByCode($address->getShippingMethod());

        return $rate;
    }
}
