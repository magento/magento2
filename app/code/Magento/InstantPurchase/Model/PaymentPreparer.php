<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model;

use Exception;
use Magento\Braintree\Model\Ui\ConfigProvider as BrainTreeConfigProvider;
use Magento\Quote\Model\Quote;

class PaymentPreparer
{
    /**
     * @var CustomerCreditCardManager
     */
    private $customerCreditCardManager;

    /**
     * PaymentPreparer constructor.
     * @param CustomerCreditCardManager $customerCreditCardManager
     */
    public function __construct(
        CustomerCreditCardManager $customerCreditCardManager
    ) {
        $this->customerCreditCardManager = $customerCreditCardManager;
    }

    /**
     * @param Quote $quote
     * @param string $customerId
     * @param string $ccId
     * @throws Exception
     */
    public function prepare(Quote $quote, string $customerId, string $ccId)
    {
        $cc = $this->customerCreditCardManager->getCustomerCreditCard($customerId, $ccId);
        $publicHash = $cc->getPublicHash();
        $quote->getPayment()->setQuote($quote)->importData(
            ['method' => BrainTreeConfigProvider::CC_VAULT_CODE]
        )->setAdditionalInformation(
            $this->customerCreditCardManager->getPaymentAdditionalInformation($customerId, $publicHash)
        );
        $quote->collectTotals();
    }
}
