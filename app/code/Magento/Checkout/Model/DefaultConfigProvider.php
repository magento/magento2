<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model;

use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Checkout\Model\Type\Onepage as OnepageCheckout;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Registration as CustomerRegistration;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url as CustomerUrlManager;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Locale\CurrencyInterface as CurrencyManager;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Api\CartItemRepositoryInterface as QuoteItemRepository;
use Magento\Quote\Api\ShippingMethodManagementInterface as ShippingMethodManager;
use Magento\Catalog\Helper\Product\ConfigurationPool;


class DefaultConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CheckoutHelper
     */
    private $checkoutHelper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CustomerRegistration
     */
    private $customerRegistration;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CustomerUrlManager
     */
    private $customerUrlManager;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var CurrencyManager
     */
    private $currencyManager;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var QuoteItemRepository
     */
    private $quoteItemRepository;

    /**
     * @var ShippingMethodManager
     */
    private $shippingMethodManager;

    /**
     * @var ConfigurationPool
     */
    private $configurationPool;

    /**
     * @param CheckoutHelper $checkoutHelper
     * @param Session $checkoutSession
     * @param CustomerRegistration $customerRegistration
     * @param CustomerRepository $customerRepository
     * @param CustomerSession $customerSession
     * @param CustomerUrlManager $customerUrlManager
     * @param HttpContext $httpContext
     * @param CurrencyManager $currencyManager
     * @param QuoteRepository $quoteRepository
     * @param QuoteItemRepository $quoteItemRepository
     * @param ShippingMethodManager $shippingMethodManager
     * @param ConfigurationPool $configurationPool
     */
    public function __construct(
        CheckoutHelper $checkoutHelper,
        CheckoutSession $checkoutSession,
        CustomerRegistration $customerRegistration,
        CustomerRepository $customerRepository,
        CustomerSession $customerSession,
        CustomerUrlManager $customerUrlManager,
        HttpContext $httpContext,
        CurrencyManager $currencyManager,
        QuoteRepository $quoteRepository,
        QuoteItemRepository $quoteItemRepository,
        ShippingMethodManager $shippingMethodManager,
        ConfigurationPool $configurationPool
    ) {
        $this->checkoutHelper = $checkoutHelper;
        $this->checkoutSession = $checkoutSession;
        $this->customerRepository = $customerRepository;
        $this->customerRegistration = $customerRegistration;
        $this->customerSession = $customerSession;
        $this->customerUrlManager = $customerUrlManager;
        $this->httpContext = $httpContext;
        $this->currencyManager = $currencyManager;
        $this->quoteRepository = $quoteRepository;
        $this->quoteItemRepository = $quoteItemRepository;
        $this->shippingMethodManager = $shippingMethodManager;
        $this->configurationPool = $configurationPool;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'customerData' => $this->getCustomerData(),
            'quoteData' => $this->getQuoteData(),
            'quoteItemData' => $this->getQuoteItemData(),
            'isCustomerLoggedIn' => $this->isCustomerLoggedIn(),
            'currencySymbol' => $this->getCurrencySymbol(),
            'baseCurrencySymbol' => $this->getBaseCurrencySymbol(),
            'selectedShippingMethod' => $this->getSelectedShippingMethod(),
            'storeCode' => $this->getStoreCode(),
            'isGuestCheckoutAllowed' => $this->isGuestCheckoutAllowed(),
            'isRegistrationAllowed' => $this->isRegistrationAllowed(),
            'isMethodRegister' => $this->isMethodRegister(),
            'isCustomerLoginRequired' => $this->isCustomerLoginRequired(),
            'registerUrl' => $this->getRegisterUrl(),
            'customerAddressCount' => $this->getCustomerAddressCount(),
        ];
    }

    /**
     * Retrieve customer data
     *
     * @return array
     */
    private function getCustomerData()
    {
        $customerData = [];
        if ($this->isCustomerLoggedIn()) {
            $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
            $customerData = $customer->__toArray();
        }
        return $customerData;
    }

    /**
     * Retrieve number of customer addresses
     *
     * @return int
     */
    private function getCustomerAddressCount()
    {
        $customerAddressCount = 0;
        if ($this->isCustomerLoggedIn()) {
            $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
            $addresses = $customer->getAddresses();
            $customerAddressCount = count($addresses);
        }
        return $customerAddressCount;
    }

    /**
     * Retrieve quote data
     *
     * @return array
     */
    private function getQuoteData()
    {
        $quoteData = [];
        if ($this->getQuote()->getId()) {
            $quote = $this->quoteRepository->get($this->getQuote()->getId());
            // the following condition is a legacy logic left here for compatibility
            if (!$quote->getCustomer()->getId()) {
                $this->quoteRepository->save($this->getQuote()->setCheckoutMethod('guest'));
            } else {
                $this->quoteRepository->save($this->getQuote()->setCheckoutMethod(null));
            }

            $quoteData = $quote->toArray();
        }
        return $quoteData;
    }

    /**
     * Retrieve quote item data
     *
     * @return array
     */
    private function getQuoteItemData()
    {
        $quoteItemData = [];
        $quoteId = $this->getQuote()->getId();
        if ($quoteId) {
            $quoteItems = $this->quoteItemRepository->getList($quoteId);
            foreach($quoteItems as $index => $quoteItem) {
                $quoteItemData[$index] = $quoteItem->toArray();
                $quoteItemData[$index]['options'] = $this->getFormattedOptionValue($quoteItem);
            }
        }
        return $quoteItemData;
    }

    /**
     * Retrieve formatted item options view
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return array
     */
    protected function getFormattedOptionValue($item)
    {
        $optionsData = [];
        $options = $this->configurationPool->getByProductType($item->getProductType())->getOptions($item);
        foreach ($options as $index => $optionValue) {
            /* @var $helper \Magento\Catalog\Helper\Product\Configuration */
            $helper = $this->configurationPool->getByProductType('default');
            $params = [
                'max_length' => 55,
                'cut_replacer' => ' <a href="#" class="dots tooltip toggle" onclick="return false">...</a>'
            ];
            $option = $helper->getFormattedOptionValue($optionValue, $params);
            $optionsData[$index] = $option;
            $optionsData[$index]['label'] = $optionValue['label'];
        }
        return $optionsData;
    }

    /**
     * Retrieve active quote currency symbol
     *
     * @return string
     */
    private function getCurrencySymbol()
    {
        $currencySymbol = '';
        if ($this->getQuote()->getId()) {
            $quote = $this->quoteRepository->get($this->getQuote()->getId());
            $currency = $this->currencyManager->getCurrency($quote->getQuoteCurrencyCode());
            $currencySymbol = $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();
        }
        return $currencySymbol;
    }

    /**
     * Retrieve base currency symbol
     *
     * @return string
     */
    private function getBaseCurrencySymbol()
    {
        $defaultCurrency = $this->currencyManager->getCurrency($this->currencyManager->getDefaultCurrency());
        $currencySymbol = $defaultCurrency->getSymbol()
            ? $defaultCurrency->getSymbol() : $defaultCurrency->getShortName();
        return $currencySymbol;
    }

    /**
     * Retrieve customer registration URL
     *
     * @return string
     */
    public function getRegisterUrl()
    {
        return $this->customerUrlManager->getRegisterUrl();
    }

    /**
     * Retrieve selected shipping method
     *
     * @return string
     */
    private function getSelectedShippingMethod()
    {
        // Shipping method ID contains carrier code and shipping method code
        $shippingMethodId = '';
        try {
            $quoteId = $this->getQuote()->getId();
            $shippingMethod = $this->shippingMethodManager->get($quoteId);
            if ($shippingMethod) {
                $shippingMethodId = $shippingMethod->getCarrierCode() . '_' . $shippingMethod->getMethodCode();
            }
        } catch (\Exception $exception) {
            $shippingMethodId = '';
        }
        return $shippingMethodId;
    }

    /**
     * Retrieve store code
     *
     * @return string
     */
    private function getStoreCode()
    {
        return $this->checkoutSession->getQuote()->getStore()->getCode();
    }

    /**
     * Check if guest checkout is allowed
     *
     * @return bool
     */
    private function isGuestCheckoutAllowed()
    {
        return $this->checkoutHelper->isAllowedGuestCheckout($this->checkoutSession->getQuote());
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    private function isCustomerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * Check if customer must be logged in to proceed with checkout
     *
     * @return bool
     */
    private function isCustomerLoginRequired()
    {
        return $this->checkoutHelper->isCustomerMustBeLogged();
    }

    /**
     * Check if customer registration is allowed
     *
     * @return bool
     */
    private function isRegistrationAllowed()
    {
        return $this->customerRegistration->isAllowed();
    }

    /**
     * Check if checkout method is 'Register'
     *
     * @return bool
     */
    private function isMethodRegister()
    {
        return $this->checkoutSession->getQuote()->getCheckoutMethod() == OnepageCheckout::METHOD_REGISTER;
    }

    /**
     * Retrieve current quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    private function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }
}
