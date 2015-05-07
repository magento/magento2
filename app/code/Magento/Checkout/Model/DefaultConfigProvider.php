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
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Framework\Locale\FormatInterface as LocaleFormat;

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
     * @param QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var LocaleFormat
     */
    protected $localeFormat;

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
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param LocaleFormat $localeFormat
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
        ConfigurationPool $configurationPool,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        LocaleFormat $localeFormat
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
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->localeFormat = $localeFormat;
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
            'baseCurrencySymbol' => $this->getBaseCurrencySymbol(),
            'selectedShippingMethod' => $this->getSelectedShippingMethod(),
            'storeCode' => $this->getStoreCode(),
            'isGuestCheckoutAllowed' => $this->isGuestCheckoutAllowed(),
            'isRegistrationAllowed' => $this->isRegistrationAllowed(),
            'isMethodRegister' => $this->isMethodRegister(),
            'isCustomerLoginRequired' => $this->isCustomerLoginRequired(),
            'registerUrl' => $this->getRegisterUrl(),
            'customerAddressCount' => $this->getCustomerAddressCount(),
            'forgotPasswordUrl' => $this->getForgotPasswordUrl(),
            'priceFormat' => $this->localeFormat->getPriceFormat(
                null,
                $this->checkoutSession->getQuote()->getQuoteCurrencyCode()
            )
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
        if ($this->checkoutSession->getQuote()->getId()) {
            $quote = $this->quoteRepository->get($this->checkoutSession->getQuote()->getId());
            // the following condition is a legacy logic left here for compatibility
            if (!$quote->getCustomer()->getId()) {
                $this->quoteRepository->save($this->checkoutSession->getQuote()->setCheckoutMethod('guest'));
            } else {
                $this->quoteRepository->save($this->checkoutSession->getQuote()->setCheckoutMethod(null));
            }

            $quoteData = $quote->toArray();
            $quoteData['is_virtual'] = $quote->getIsVirtual();

            /**
             * Temporary workaround for guest customer API issue.
             */
            if (!$quote->getCustomer()->getId()) {
                /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
                $quoteIdMask = $this->quoteIdMaskFactory->create();
                $quoteData['entity_id'] = $quoteIdMask->load(
                    $this->checkoutSession->getQuote()->getId()
                )->getMaskedId();
            }

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
        $quoteId = $this->checkoutSession->getQuote()->getId();
        if ($quoteId) {
            $quoteItems = $this->quoteItemRepository->getList($quoteId);
            foreach ($quoteItems as $index => $quoteItem) {
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
            $quoteId = $this->checkoutSession->getQuote()->getId();
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
     * Return forgot password URL
     *
     * @return string
     */
    private function getForgotPasswordUrl()
    {
        return $this->customerUrlManager->getForgotPasswordUrl();
    }
}
