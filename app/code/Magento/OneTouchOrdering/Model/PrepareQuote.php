<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Braintree\Model\Ui\ConfigProvider as BrainTreeConfigProvider;

class PrepareQuote
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\OneTouchOrdering\Model\CustomerBrainTreeManager
     */
    protected $customerBrainTreeManager;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\OneTouchOrdering\Model\CustomerBrainTreeManager $customerBrainTreeManager
    ) {

        $this->customerSession = $customerSession;
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
        $this->customerBrainTreeManager = $customerBrainTreeManager;
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function prepare()
    {
        $store = $this->storeManager->getStore();
        $quote = $this->quoteFactory->create();
        $customer = $this->customerSession->getCustomer();

        $quote->setStore($store);
        $quote->setCurrency();
        $quote->assignCustomer($customer->getDataModel());
        $quote->getBillingAddress()->importCustomerAddressData(
            $customer->getDefaultBillingAddress()->getDataModel()
        );
        $quote->getShippingAddress()->importCustomerAddressData(
            $customer->getDefaultShippingAddress()->getDataModel()
        );
        $quote->setInventoryProcessed(false);

        return $quote;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function preparePayment(\Magento\Quote\Model\Quote $quote)
    {
        $customerId = $this->customerSession->getCustomerId();
        $cc = $this->customerBrainTreeManager->getCustomerBrainTreeCard($customerId);

        if (!$cc) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('There are no credit cards available.')
            );
        }
        $publicHash = $cc->getPublicHash();
        $quote->getPayment()->setQuote($quote)->importData(
            ['method' => BrainTreeConfigProvider::CC_VAULT_CODE]
        )->setAdditionalInformation([
                'customer_id' => $customerId,
                'public_hash' => $publicHash,
                'payment_method_nonce' => $this->customerBrainTreeManager->getNonce($publicHash, $customerId),
                'is_active_payment_token_enabler' => true
        ]);
        $quote->collectTotals();
    }
}
