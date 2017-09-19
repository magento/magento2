<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Braintree\Model\Ui\ConfigProvider as BrainTreeConfigProvider;
use Magento\Framework\Exception\LocalizedException;
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
     * @var CustomerBrainTreeManager
     */
    private $customerBrainTreeManager;
    /**
     * @var CustomerData
     */
    private $customerData;

    public function __construct(
        CustomerData $customerData,
        QuoteFactory $quoteFactory,
        StoreManagerInterface $storeManager,
        CustomerBrainTreeManager $customerBrainTreeManager
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
        $this->customerBrainTreeManager = $customerBrainTreeManager;
        $this->customerData = $customerData;
    }

    /**
     * @return Quote
     */
    public function prepare()
    {
        $store = $this->storeManager->getStore();
        $quote = $this->quoteFactory->create();

        $quote->setStore($store);
        $quote->setCurrency();
        $quote->assignCustomer($this->customerData->getCustomerDataModel());
        $quote->getBillingAddress()->importCustomerAddressData(
            $this->customerData->getDefaultBillingAddressDataModel()
        );
        $quote->getShippingAddress()->importCustomerAddressData(
            $this->customerData->getDefaultShippingAddressDataModel()
        );
        $quote->setInventoryProcessed(false);

        return $quote;
    }

    /**
     * @param Quote $quote
     * @throws LocalizedException
     */
    public function preparePayment(Quote $quote)
    {
        $customerId = $this->customerData->getCustomerId();
        $cc = $this->customerBrainTreeManager->getCustomerBrainTreeCard($customerId);

        if (!$cc) {
            throw new LocalizedException(
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
