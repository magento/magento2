<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model;

use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url as CustomerUrlManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Locale\FormatInterface as LocaleFormat;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartItemRepositoryInterface as QuoteItemRepository;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface as ShippingMethodManager;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since 2.0.0
 */
class DefaultConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CheckoutHelper
     * @since 2.0.0
     */
    private $checkoutHelper;

    /**
     * @var CheckoutSession
     * @since 2.0.0
     */
    private $checkoutSession;

    /**
     * @var CustomerRepository
     * @since 2.0.0
     */
    private $customerRepository;

    /**
     * @var CustomerSession
     * @since 2.0.0
     */
    private $customerSession;

    /**
     * @var CustomerUrlManager
     * @since 2.0.0
     */
    private $customerUrlManager;

    /**
     * @var HttpContext
     * @since 2.0.0
     */
    private $httpContext;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     * @since 2.0.0
     */
    private $quoteRepository;

    /**
     * @var QuoteItemRepository
     * @since 2.0.0
     */
    private $quoteItemRepository;

    /**
     * @var ShippingMethodManager
     * @since 2.0.0
     */
    private $shippingMethodManager;

    /**
     * @var ConfigurationPool
     * @since 2.0.0
     */
    private $configurationPool;

    /**
     * @param QuoteIdMaskFactory
     * @since 2.0.0
     */
    protected $quoteIdMaskFactory;

    /**
     * @var LocaleFormat
     * @since 2.0.0
     */
    protected $localeFormat;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     * @since 2.0.0
     */
    protected $addressMapper;

    /**
     * @var \Magento\Customer\Model\Address\Config
     * @since 2.0.0
     */
    protected $addressConfig;

    /**
     * @var FormKey
     * @since 2.0.0
     */
    protected $formKey;

    /**
     * @var \Magento\Catalog\Helper\Image
     * @since 2.0.0
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\View\ConfigInterface
     * @since 2.0.0
     */
    protected $viewConfig;

    /**
     * @var \Magento\Directory\Model\Country\Postcode\ConfigInterface
     * @since 2.0.0
     */
    protected $postCodesConfig;

    /**
     * @var \Magento\Directory\Helper\Data
     * @since 2.0.0
     */
    protected $directoryHelper;

    /**
     * @var Cart\ImageProvider
     * @since 2.0.0
     */
    protected $imageProvider;

    /**
     * @var CartTotalRepositoryInterface
     * @since 2.0.0
     */
    protected $cartTotalRepository;

    /**
     * @var ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Shipping\Model\Config
     * @since 2.0.0
     */
    protected $shippingMethodConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     * @since 2.0.0
     */
    protected $paymentMethodManagement;

    /**
     * @var UrlInterface
     * @since 2.0.0
     */
    protected $urlBuilder;

    /**
     * @param CheckoutHelper $checkoutHelper
     * @param Session $checkoutSession
     * @param CustomerRepository $customerRepository
     * @param CustomerSession $customerSession
     * @param CustomerUrlManager $customerUrlManager
     * @param HttpContext $httpContext
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param QuoteItemRepository $quoteItemRepository
     * @param ShippingMethodManager $shippingMethodManager
     * @param ConfigurationPool $configurationPool
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param LocaleFormat $localeFormat
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param FormKey $formKey
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\View\ConfigInterface $viewConfig
     * @param \Magento\Directory\Model\Country\Postcode\ConfigInterface $postCodesConfig
     * @param Cart\ImageProvider $imageProvider
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Shipping\Model\Config $shippingMethodConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param UrlInterface $urlBuilder
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        CheckoutHelper $checkoutHelper,
        CheckoutSession $checkoutSession,
        CustomerRepository $customerRepository,
        CustomerSession $customerSession,
        CustomerUrlManager $customerUrlManager,
        HttpContext $httpContext,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        QuoteItemRepository $quoteItemRepository,
        ShippingMethodManager $shippingMethodManager,
        ConfigurationPool $configurationPool,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        LocaleFormat $localeFormat,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Magento\Customer\Model\Address\Config $addressConfig,
        FormKey $formKey,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\View\ConfigInterface $viewConfig,
        \Magento\Directory\Model\Country\Postcode\ConfigInterface $postCodesConfig,
        Cart\ImageProvider $imageProvider,
        \Magento\Directory\Helper\Data $directoryHelper,
        CartTotalRepositoryInterface $cartTotalRepository,
        ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Config $shippingMethodConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        UrlInterface $urlBuilder
    ) {
        $this->checkoutHelper = $checkoutHelper;
        $this->checkoutSession = $checkoutSession;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->customerUrlManager = $customerUrlManager;
        $this->httpContext = $httpContext;
        $this->quoteRepository = $quoteRepository;
        $this->quoteItemRepository = $quoteItemRepository;
        $this->shippingMethodManager = $shippingMethodManager;
        $this->configurationPool = $configurationPool;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->localeFormat = $localeFormat;
        $this->addressMapper = $addressMapper;
        $this->addressConfig = $addressConfig;
        $this->formKey = $formKey;
        $this->imageHelper = $imageHelper;
        $this->viewConfig = $viewConfig;
        $this->postCodesConfig = $postCodesConfig;
        $this->imageProvider = $imageProvider;
        $this->directoryHelper = $directoryHelper;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->scopeConfig = $scopeConfig;
        $this->shippingMethodConfig = $shippingMethodConfig;
        $this->storeManager = $storeManager;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getConfig()
    {
        $quoteId = $this->checkoutSession->getQuote()->getId();
        $output['formKey'] = $this->formKey->getFormKey();
        $output['customerData'] = $this->getCustomerData();
        $output['quoteData'] = $this->getQuoteData();
        $output['quoteItemData'] = $this->getQuoteItemData();
        $output['isCustomerLoggedIn'] = $this->isCustomerLoggedIn();
        $output['selectedShippingMethod'] = $this->getSelectedShippingMethod();
        $output['storeCode'] = $this->getStoreCode();
        $output['isGuestCheckoutAllowed'] = $this->isGuestCheckoutAllowed();
        $output['isCustomerLoginRequired'] = $this->isCustomerLoginRequired();
        $output['registerUrl'] = $this->getRegisterUrl();
        $output['checkoutUrl'] = $this->getCheckoutUrl();
        $output['defaultSuccessPageUrl'] = $this->getDefaultSuccessPageUrl();
        $output['pageNotFoundUrl'] = $this->pageNotFoundUrl();
        $output['forgotPasswordUrl'] = $this->getForgotPasswordUrl();
        $output['staticBaseUrl'] = $this->getStaticBaseUrl();
        $output['priceFormat'] = $this->localeFormat->getPriceFormat(
            null,
            $this->checkoutSession->getQuote()->getQuoteCurrencyCode()
        );
        $output['basePriceFormat'] = $this->localeFormat->getPriceFormat(
            null,
            $this->checkoutSession->getQuote()->getBaseCurrencyCode()
        );
        $output['postCodes'] = $this->postCodesConfig->getPostCodes();
        $output['imageData'] = $this->imageProvider->getImages($quoteId);
        $output['totalsData'] = $this->getTotalsData();
        $output['shippingPolicy'] = [
            'isEnabled' => $this->scopeConfig->isSetFlag(
                'shipping/shipping_policy/enable_shipping_policy',
                ScopeInterface::SCOPE_STORE
            ),
            'shippingPolicyContent' => nl2br(
                $this->scopeConfig->getValue(
                    'shipping/shipping_policy/shipping_policy_content',
                    ScopeInterface::SCOPE_STORE
                )
            )
        ];
        $output['activeCarriers'] = $this->getActiveCarriers();
        $output['originCountryCode'] = $this->getOriginCountryCode();
        $output['paymentMethods'] = $this->getPaymentMethods();
        $output['autocomplete'] = $this->isAutocompleteEnabled();
        $output['displayBillingOnPaymentMethod'] = $this->checkoutHelper->isDisplayBillingOnPaymentMethodAvailable();
        return $output;
    }

    /**
     * Is autocomplete enabled for storefront
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    private function isAutocompleteEnabled()
    {
        return $this->scopeConfig->getValue(
            \Magento\Customer\Model\Form::XML_PATH_ENABLE_AUTOCOMPLETE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ? 'on' : 'off';
    }

    /**
     * Retrieve customer data
     *
     * @return array
     * @since 2.0.0
     */
    private function getCustomerData()
    {
        $customerData = [];
        if ($this->isCustomerLoggedIn()) {
            $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
            $customerData = $customer->__toArray();
            foreach ($customer->getAddresses() as $key => $address) {
                $customerData['addresses'][$key]['inline'] = $this->getCustomerAddressInline($address);
            }
        }
        return $customerData;
    }

    /**
     * Set additional customer address data
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return string
     * @since 2.0.0
     */
    private function getCustomerAddressInline($address)
    {
        $builtOutputAddressData = $this->addressMapper->toFlatArray($address);
        return $this->addressConfig
            ->getFormatByCode(\Magento\Customer\Model\Address\Config::DEFAULT_ADDRESS_FORMAT)
            ->getRenderer()
            ->renderArray($builtOutputAddressData);
    }

    /**
     * Retrieve quote data
     *
     * @return array
     * @since 2.0.0
     */
    private function getQuoteData()
    {
        $quoteData = [];
        if ($this->checkoutSession->getQuote()->getId()) {
            $quote = $this->quoteRepository->get($this->checkoutSession->getQuote()->getId());
            $quoteData = $quote->toArray();
            $quoteData['is_virtual'] = $quote->getIsVirtual();

            if (!$quote->getCustomer()->getId()) {
                /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
                $quoteIdMask = $this->quoteIdMaskFactory->create();
                $quoteData['entity_id'] = $quoteIdMask->load(
                    $this->checkoutSession->getQuote()->getId(),
                    'quote_id'
                )->getMaskedId();
            }
        }
        return $quoteData;
    }

    /**
     * Retrieve quote item data
     *
     * @return array
     * @since 2.0.0
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
                $quoteItemData[$index]['thumbnail'] = $this->imageHelper->init(
                    $quoteItem->getProduct(),
                    'product_thumbnail_image'
                )->getUrl();
            }
        }
        return $quoteItemData;
    }

    /**
     * Retrieve formatted item options view
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return array
     * @since 2.0.0
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
     * Retrieve customer registration URL
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getRegisterUrl()
    {
        return $this->customerUrlManager->getRegisterUrl();
    }

    /**
     * Retrieve checkout URL
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getCheckoutUrl()
    {
        return $this->urlBuilder->getUrl('checkout');
    }

    /**
     * Retrieve checkout URL
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function pageNotFoundUrl()
    {
        return $this->urlBuilder->getUrl('checkout/noroute');
    }

    /**
     * Retrieve default success page URL
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.1.0
     */
    public function getDefaultSuccessPageUrl()
    {
        return $this->urlBuilder->getUrl('checkout/onepage/success/');
    }

    /**
     * Retrieve selected shipping method
     *
     * @return array|null
     * @since 2.0.0
     */
    private function getSelectedShippingMethod()
    {
        $shippingMethodData = null;
        try {
            $quoteId = $this->checkoutSession->getQuote()->getId();
            $shippingMethod = $this->shippingMethodManager->get($quoteId);
            if ($shippingMethod) {
                $shippingMethodData = $shippingMethod->__toArray();
            }
        } catch (\Exception $exception) {
            $shippingMethodData = null;
        }
        return $shippingMethodData;
    }

    /**
     * Retrieve store code
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    private function getStoreCode()
    {
        return $this->checkoutSession->getQuote()->getStore()->getCode();
    }

    /**
     * Check if guest checkout is allowed
     *
     * @return bool
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    private function isGuestCheckoutAllowed()
    {
        return $this->checkoutHelper->isAllowedGuestCheckout($this->checkoutSession->getQuote());
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    private function isCustomerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * Check if customer must be logged in to proceed with checkout
     *
     * @return bool
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    private function isCustomerLoginRequired()
    {
        return $this->checkoutHelper->isCustomerMustBeLogged();
    }

    /**
     * Return forgot password URL
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    private function getForgotPasswordUrl()
    {
        return $this->customerUrlManager->getForgotPasswordUrl();
    }

    /**
     * Return base static url.
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    protected function getStaticBaseUrl()
    {
        return $this->checkoutSession->getQuote()->getStore()->getBaseUrl(UrlInterface::URL_TYPE_STATIC);
    }

    /**
     * Return quote totals data
     * @return array
     * @since 2.0.0
     */
    private function getTotalsData()
    {
        /** @var \Magento\Quote\Api\Data\TotalsInterface $totals */
        $totals = $this->cartTotalRepository->get($this->checkoutSession->getQuote()->getId());
        $items = [];
        /** @var  \Magento\Quote\Model\Cart\Totals\Item $item */
        foreach ($totals->getItems() as $item) {
            $items[] = $item->__toArray();
        }
        $totalSegmentsData = [];
        /** @var \Magento\Quote\Model\Cart\TotalSegment $totalSegment */
        foreach ($totals->getTotalSegments() as $totalSegment) {
            $totalSegmentArray = $totalSegment->toArray();
            if (is_object($totalSegment->getExtensionAttributes())) {
                $totalSegmentArray['extension_attributes'] = $totalSegment->getExtensionAttributes()->__toArray();
            }
            $totalSegmentsData[] = $totalSegmentArray;
        }
        $totals->setItems($items);
        $totals->setTotalSegments($totalSegmentsData);
        $totalsArray = $totals->toArray();
        if (is_object($totals->getExtensionAttributes())) {
            $totalsArray['extension_attributes'] = $totals->getExtensionAttributes()->__toArray();
        }
        return $totalsArray;
    }

    /**
     * Returns active carriers codes
     * @return array
     * @since 2.0.0
     */
    private function getActiveCarriers()
    {
        $activeCarriers = [];
        foreach ($this->shippingMethodConfig->getActiveCarriers() as $carrier) {
            $activeCarriers[] = $carrier->getCarrierCode();
        }
        return $activeCarriers;
    }

    /**
     * Returns origin country code
     * @return string
     * @since 2.0.0
     */
    private function getOriginCountryCode()
    {
        return $this->scopeConfig->getValue(
            \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_COUNTRY_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()
        );
    }

    /**
     * Returns array of payment methods
     * @return array
     * @since 2.0.0
     */
    private function getPaymentMethods()
    {
        $paymentMethods = [];
        $quote = $this->checkoutSession->getQuote();
        if ($quote->getIsVirtual()) {
            foreach ($this->paymentMethodManagement->getList($quote->getId()) as $paymentMethod) {
                $paymentMethods[] = [
                    'code' => $paymentMethod->getCode(),
                    'title' => $paymentMethod->getTitle()
                ];
            }
        }
        return $paymentMethods;
    }
}
