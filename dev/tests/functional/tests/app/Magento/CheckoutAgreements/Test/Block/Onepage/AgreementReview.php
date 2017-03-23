<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\Block\Onepage;

use Magento\Checkout\Test\Block\Onepage\Payment;
use Magento\CheckoutAgreements\Test\Fixture\CheckoutAgreement;
use Magento\Mtf\Client\Locator;

/**
 * Class AgreementReview
 * One page checkout order review block
 */
class AgreementReview extends Payment
{
    /**
     * Notification agreements locator
     *
     * @var string
     */
    protected $notification = 'div.mage-error';

    /**
     * Agreement locator
     *
     * @var string
     */
    protected $agreement = '//label[.="%s"]';

    /**
     * Agreement checkbox locator
     *
     * @var string
     */
    protected $agreementCheckbox = '//label[contains(., "%s")]//../input';

    /**
     * Get notification massage
     *
     * @return string
     */
    public function getNotificationMassage()
    {
        return $this->_rootElement->find($this->notification)->getText();
    }

    /**
     * Set agreement
     *
     * @param string $value
     * @param CheckoutAgreement $agreement
     * @return void
     */
    public function setAgreement($value, CheckoutAgreement $agreement)
    {
        $this->getSelectedPaymentMethodBlock()->_rootElement->find(
            sprintf($this->agreementCheckbox, $agreement->getCheckboxText()),
            Locator::SELECTOR_XPATH,
            'checkbox'
        )->setValue($value);
    }

    /**
     * Check agreement
     *
     * @param CheckoutAgreement $agreement
     * @return bool
     */
    public function checkAgreement(CheckoutAgreement $agreement)
    {
        return $this->_rootElement
            ->find(sprintf($this->agreement, $agreement->getCheckboxText()), Locator::SELECTOR_XPATH)->isVisible();
    }
}
