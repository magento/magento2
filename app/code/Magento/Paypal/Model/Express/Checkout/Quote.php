<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Model\Express\Checkout;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressDataBuilder;
use Magento\Customer\Api\Data\CustomerDataBuilder;
use Magento\Framework\Object\Copy as CopyObject;

/**
 * Class Quote
 */
class Quote
{
    /**
     * @var AddressDataBuilder
     */
    protected $addressBuilder;

    /**
     * @var CustomerDataBuilder
     */
    protected $customerBuilder;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CopyObject
     */
    protected $copyObject;

    /**
     * @param AddressDataBuilder $addressBuilder
     * @param CustomerDataBuilder $customerBuilder
     * @param CustomerRepositoryInterface $customerRepository
     * @param CopyObject $copyObject
     */
    public function __construct(
        AddressDataBuilder $addressBuilder,
        CustomerDataBuilder $customerBuilder,
        CustomerRepositoryInterface $customerRepository,
        CopyObject $copyObject
    ) {
        $this->addressBuilder = $addressBuilder;
        $this->customerBuilder = $customerBuilder;
        $this->customerRepository = $customerRepository;
        $this->copyObject = $copyObject;
    }

    /**
     * @param \Magento\Sales\Model\Quote $quote
     * @return \Magento\Sales\Model\Quote
     */
    public function prepareQuoteForNewCustomer(\Magento\Sales\Model\Quote $quote)
    {
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();
        $billing->setDefaultBilling(true);
        if ($shipping && !$shipping->getSameAsBilling()) {
            $shipping->setDefaultShipping(true);
            $shipping->setCustomerAddressData(
                $this->addressBuilder->populateWithArray($shipping->getData())->create()
            );
        } elseif ($shipping) {
            $billing->setDefaultShipping(true);
        }
        $billing->setCustomerAddressData(
            $this->addressBuilder->populateWithArray($shipping->getData())->create()
        );
        foreach (['customer_dob', 'customer_taxvat', 'customer_gender'] as $attribute) {
            if ($quote->getData($attribute) && !$billing->getData($attribute)) {
                $billing->setData($attribute, $quote->getData($attribute));
            }
        }
        $this->customerBuilder->populateWithArray(
            $this->copyObject->getDataFromFieldset(
                'checkout_onepage_billing',
                'to_customer',
                $billing
            )
        );
        $this->customerBuilder->setEmail($quote->getCustomerEmail());
        $this->customerBuilder->setPrefix($quote->getCustomerPrefix());
        $this->customerBuilder->setFirstname($quote->getCustomerFirstname());
        $this->customerBuilder->setMiddlename($quote->getCustomerMiddlename());
        $this->customerBuilder->setLastname($quote->getCustomerLastname());
        $this->customerBuilder->setSuffix($quote->getCustomerSuffix());
        $quote->setCustomer($this->customerBuilder->create());
        $quote->addCustomerAddress($billing->exportCustomerAddress());
        if ($shipping->hasCustomerAddress()) {
            $quote->addCustomerAddress($shipping->exportCustomerAddress());
        }
        return $quote;
    }

    /**
     * @param \Magento\Sales\Model\Quote $quote
     * @param int|null $customerId
     * @return \Magento\Sales\Model\Quote
     */
    public function prepareRegisteredCustomerQuote(\Magento\Sales\Model\Quote $quote, $customerId)
    {
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();
        $customer = $this->customerRepository->getById($customerId);
        if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
            $billing->setCustomerAddressData(
                $this->addressBuilder->populateWithArray($billing->getData())->create()
            );
        }
        if ($shipping && !$shipping->getSameAsBilling()
            && (!$shipping->getCustomerId() || $shipping->getSaveInAddressBook())
        ) {
            $shipping->setCustomerAddressData(
                $this->addressBuilder->populateWithArray($shipping->getData())->create()
            );
        }
        $isBillingAddressDefaultBilling = !!$customer->getDefaultBilling();
        $isBillingAddressDefaultShipping = false;
        if ($shipping && !$customer->getDefaultShipping()) {
            $shipping->setDefaultBilling(false);
            $shipping->setDefaultShipping(true);
            $quote->addCustomerAddress($this->addressBuilder->populateWithArray($shipping->getData())->create());
        } elseif (!$customer->getDefaultShipping()) {
            $isBillingAddressDefaultShipping = true;
        }
        if ($billing) {
            $billing->setDefaultBilling($isBillingAddressDefaultBilling);
            $billing->setDefaultShipping($isBillingAddressDefaultShipping);
            $quote->addCustomerAddress($this->addressBuilder->populateWithArray($billing->getData())->create());
        }
        $quote->setCustomer($customer);
        return $quote;
    }
}
