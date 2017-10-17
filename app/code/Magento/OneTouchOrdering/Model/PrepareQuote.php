<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Exception;
use Magento\Braintree\Model\Ui\ConfigProvider as BrainTreeConfigProvider;
use Magento\Framework\DataObject;
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
     * @param CustomerDataGetter $customerData
     * @param DataObject $params
     * @return Quote
     */
    public function prepare(CustomerDataGetter $customerData, DataObject $params): Quote
    {
        $store = $this->storeManager->getStore();
        $quote = $this->quoteFactory->create();

        $quote->setStore($store);
        $quote->setCurrency();
        $quote->assignCustomer($customerData->getCustomerDataModel());
        $quote->getBillingAddress()->importCustomerAddressData(
            $customerData->getDefaultBillingAddressDataModel()
        );

        if ($addressId = $params->getCustomerAddress()) {
            $shippingAddressData = $customerData->getShippingAddressDataModel($addressId);
        } else {
            $shippingAddressData = $customerData->getDefaultShippingAddressDataModel();
        }

        $quote->getShippingAddress()->importCustomerAddressData(
            $shippingAddressData
        );
        $quote->setInventoryProcessed(false);

        return $quote;
    }

    /**
     * @param Quote $quote
     * @param string $customerId
     * @param string $ccId
     * @throws Exception
     */
    public function preparePayment(Quote $quote, string $customerId, string $ccId)
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
