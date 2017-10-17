<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class QuotePreparer
 * @api
 */
class QuotePreparer
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
     * PrepareQuote constructor.
     * @param QuoteFactory $quoteFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
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
        $shippingAddressData = $this->getShippingAddressData($params, $customerData);
        $quote->getShippingAddress()->importCustomerAddressData(
            $shippingAddressData
        );
        $quote->setInventoryProcessed(false);

        return $quote;
    }

    /**
     * @param DataObject $params
     * @param CustomerDataGetter $customerData
     * @return AddressInterface
     */
    private function getShippingAddressData(DataObject $params, CustomerDataGetter $customerData): AddressInterface
    {
        if ($addressId = $params->getCustomerAddress()) {
            return $customerData->getShippingAddressDataModel($addressId);
        }

        return $customerData->getDefaultShippingAddressDataModel();
    }
}
