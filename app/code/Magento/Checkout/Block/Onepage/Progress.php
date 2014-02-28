<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Checkout
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Checkout\Block\Onepage;

use Magento\Sales\Model\Quote\Address;

/**
 * One page checkout status
 *
 * @category   Magento
 * @package    Magento_Checkout
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

        $toStep = $this->getRequest()->getParam('toStep');

        if (empty($toStep) || !isset($stepsRevertIndex[$currentStep])) {
            return $this->getCheckout()->getStepData($currentStep, 'complete');
        }

        if ($stepsRevertIndex[$currentStep] > $stepsRevertIndex[$toStep]) {
            return false;
        }

        return $this->getCheckout()->getStepData($currentStep, 'complete');
    }

    /**
     * Get quote shipping price including tax
     *
     * @return float
     */
    public function getShippingPriceInclTax()
    {
        $inclTax = $this->getQuote()->getShippingAddress()->getShippingInclTax();
        return $this->formatPrice($inclTax);
    }

    /**
     * @return string
     */
    public function getShippingPriceExclTax()
    {
        return $this->formatPrice($this->getQuote()->getShippingAddress()->getShippingAmount());
    }

    /**
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->getQuote()->getStore()->formatPrice($price);
    }
}
