<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Exception;
use Magento\Braintree\Model\Ui\ConfigProvider as BrainTreeConfigProvider;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;

class PrepareQuote
{
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var CustomerCreditCardManager
     */
    private $customerCreditCardManager;

    /**
     * PrepareQuote constructor.
     * @param QuoteFactory $quoteFactory
     * @param StoreManagerInterface $storeManager
     * @param CustomerCreditCardManager $customerCreditCardManager
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        StoreManagerInterface $storeManager,
        CustomerCreditCardManager $customerCreditCardManager
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
        $this->customerCreditCardManager = $customerCreditCardManager;
    }

    /**
     * @param CustomerData $customerData
     * @return Quote
     */
    public function prepare(CustomerData $customerData): Quote
    {
        $store = $this->storeManager->getStore();
        $quote = $this->quoteFactory->create();

        $quote->setStore($store);
        $quote->setCurrency();
        $quote->assignCustomer($customerData->getCustomerDataModel());
        $quote->getBillingAddress()->importCustomerAddressData(
            $customerData->getDefaultBillingAddressDataModel()
        );
        $quote->getShippingAddress()->importCustomerAddressData(
            $customerData->getDefaultShippingAddressDataModel()
        );
        $quote->setInventoryProcessed(false);

        return $quote;
    }

    /**
     * @param Quote $quote
     * @throws Exception
     */
    public function preparePayment(Quote $quote, $customerId)
    {
        $cc = $this->customerCreditCardManager->getCustomerCreditCard($customerId);
        $publicHash = $cc->getPublicHash();
        $quote->getPayment()->setQuote($quote)->importData(
            ['method' => BrainTreeConfigProvider::CC_VAULT_CODE]
        )->setAdditionalInformation([
                'customer_id' => $customerId,
                'public_hash' => $publicHash,
                'payment_method_nonce' => $this->customerCreditCardManager->getNonce($publicHash, $customerId),
                'is_active_payment_token_enabler' => true
        ]);
        $quote->collectTotals();
    }
}
