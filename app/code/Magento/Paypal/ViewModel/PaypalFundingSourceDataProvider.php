<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\ViewModel;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Paypal\Model\Express\Checkout;

/**
 * Provides Paypal funding source data
 *
 */
class PaypalFundingSourceDataProvider implements ArgumentInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param Session $checkoutSession
     */
    public function __construct(
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Return paypal funding source
     *
     * @return string|null
     */
    public function getPaypalFundingSource()
    {
        $quote = $this->checkoutSession->getQuote();
        if ($quote->getPayment()->getAdditionalInformation(Checkout::PAYMENT_INFO_FUNDING_SOURCE)) {
            return ucfirst($quote->getPayment()->getAdditionalInformation(
                Checkout::PAYMENT_INFO_FUNDING_SOURCE
            ));
        }
        return null;
    }
}
