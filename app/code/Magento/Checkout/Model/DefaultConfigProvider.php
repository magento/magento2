<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model;

use Magento\Captcha\Api\CaptchaConfigPostProcessorInterface;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Address\CustomerAddressDataProvider;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url as CustomerUrlManager;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Locale\FormatInterface as LocaleFormat;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartItemRepositoryInterface as QuoteItemRepository;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface as ShippingMethodManager;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Ui\Component\Form\Element\Multiline;
use Magento\Framework\Escaper;

/**
 * Default Config Provider for checkout
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class DefaultConfigProvider implements ConfigProviderInterface
{
    /**
     * @var AttributeOptionManagementInterface
     */
    private $attributeOptionManager;

    /**
     * @var CheckoutHelper
     */
    private $checkoutHelper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

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
     * @var \Magento\Quote\Api\CartRepositoryInterface
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
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var LocaleFormat
     */
    protected $localeFormat;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $addressConfig;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\View\ConfigInterface
     */
    protected $viewConfig;

    /**
     * @var \Magento\Directory\Model\Country\Postcode\ConfigInterface
     */
    protected $postCodesConfig;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var Cart\ImageProvider
     */
    protected $imageProvider;

    /**
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingMethodConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var AddressMetadataInterface
     */
    private $addressMetadata;

    /**
     * @var CustomerAddressDataProvider
     */
    private $customerAddressData;

    /**
     * @var CaptchaConfigPostProcessorInterface
     */
    private $configPostProcessor;

    /**
     * @var Escaper
     */
    private $escaper;

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
     * @param CaptchaConfigPostProcessorInterface $configPostProcessor
     * @param AddressMetadataInterface $addressMetadata
     * @param AttributeOptionManagementInterface $attributeOptionManager
     * @param CustomerAddressDataProvider|null $customerAddressData
     * @param Escaper|null $escaper
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        UrlInterface $urlBuilder,
        CaptchaConfigPostProcessorInterface $configPostProcessor,
        AddressMetadataInterface $addressMetadata = null,
        AttributeOptionManagementInterface $attributeOptionManager = null,
        CustomerAddressDataProvider $customerAddressData = null,
        Escaper $escaper = null
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
        $this->addressMetadata = $addressMetadata ?: ObjectManager::getInstance()->get(AddressMetadataInterface::class);
        $this->attributeOptionManager = $attributeOptionManager ??
            ObjectManager::getInstance()->get(AttributeOptionManagementInterface::class);
        $this->customerAddressData = $customerAddressData ?:
            ObjectManager::getInstance()->get(CustomerAddressDataProvider::class);
        $this->configPostProcessor = $configPostProcessor;
        $this->escaper = $escaper ?? ObjectManager::getInstance()->get(Escaper::class);
    }

    /**
     * Return configuration array
     *
     * @return array|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfig()
    {
        $quote = $this->checkoutSession->getQuote();
        $quoteId = $quote->getId();
        $email = $quote->getShippingAddress()->getEmail();
        $quoteItemData = $this->getQuoteItemData();
        $output['formKey'] = $this->formKey->getFormKey();
        $output['customerData'] = $this->getCustomerData();
        $output['quoteData'] = $this->getQuoteData();
        $output['quoteItemData'] = $quoteItemData;
        $output['quoteMessages'] = $this->getQuoteItemsMessages($quoteItemData);
        $output['isCustomerLoggedIn'] = $this->isCustomerLoggedIn();
        $output['selectedShippingMethod'] = $this->getSelectedShippingMethod();
        if ($email && !$this->isCustomerLoggedIn()) {
            $output['validatedEmailValue'] = $email;
        }
        if (!$this->isCustomerLoggedIn() || !$this->getCustomer()->getAddresses()) {
            $output = array_merge($output, $this->getQuoteAddressData());
        }
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
            $quote->getQuoteCurrencyCode()
        );
        $output['basePriceFormat'] = $this->localeFormat->getPriceFormat(
            null,
            $quote->getBaseCurrencyCode()
        );
        $output['postCodes'] = $this->postCodesConfig->getPostCodes();
        $output['imageData'] = $this->imageProvider->getImages($quoteId);

        $output['totalsData'] = $this->getTotalsData();

        $policyContent = $this->scopeConfig->getValue(
            'shipping/shipping_policy/shipping_policy_content',
            ScopeInterface::SCOPE_STORE
        );
        $policyContent = $this->escaper->escapeHtml($policyContent);
        $output['shippingPolicy'] = [
            'isEnabled' => $this->scopeConfig->isSetFlag(
                'shipping/shipping_policy/enable_shipping_policy',
                ScopeInterface::SCOPE_STORE
            ),
            'shippingPolicyContent' => $policyContent ? nl2br($policyContent) : ''
        ];
        $output['useQty'] = $this->scopeConfig->isSetFlag(
            'checkout/cart_link/use_qty',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $output['activeCarriers'] = $this->getActiveCarriers();
        $output['originCountryCode'] = $this->getOriginCountryCode();
        $output['paymentMethods'] = $this->getPaymentMethods();
        $output['autocomplete'] = $this->isAutocompleteEnabled();
        $output['displayBillingOnPaymentMethod'] = $this->checkoutHelper->isDisplayBillingOnPaymentMethodAvailable();
        return $this->configPostProcessor->process($output);
    }

    /**
     * Is autocomplete enabled for storefront
     *
     * @return string
     * @codeCoverageIgnore
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
     */
    private function getCustomerData(): array
    {
        $customerData = [];
        if ($this->isCustomerLoggedIn()) {
            $customer = $this->getCustomer();
            $customerData = $customer->__toArray();
            $customerData['addresses'] = $this->customerAddressData->getAddressDataByCustomer($customer);
        }
        return $customerData;
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
            $quoteData = $quote->toArray();
            if (null !== $quote->getExtensionAttributes()) {
                $quoteData['extension_attributes'] = $quote->getExtensionAttributes()->__toArray();
            }
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
                $quoteItemData[$index]['message'] = $quoteItem->getMessage();
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
     * Retrieve customer registration URL
     *
     * @return string
     * @codeCoverageIgnore
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
     */
    public function getDefaultSuccessPageUrl()
    {
        return $this->urlBuilder->getUrl('checkout/onepage/success/');
    }

    /**
     * Retrieve selected shipping method
     *
     * @return array|null
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
     * Create address data appropriate to fill checkout address form
     *
     * @param AddressInterface $address
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAddressFromData(AddressInterface $address)
    {
        $addressData = [];
        $attributesMetadata = $this->addressMetadata->getAllAttributesMetadata();
        foreach ($attributesMetadata as $attributeMetadata) {
            if (!$attributeMetadata->isVisible()) {
                continue;
            }
            $attributeCode = $attributeMetadata->getAttributeCode();
            $attributeData = $address->getData($attributeCode);
            if ($attributeData) {
                if ($attributeMetadata->getFrontendInput() === Multiline::NAME) {
                    $attributeData = \is_array($attributeData) ? $attributeData : explode("\n", $attributeData);
                    $attributeData = (object)$attributeData;
                }
                if ($attributeMetadata->isUserDefined()) {
                    $addressData[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES][$attributeCode] = $attributeData;
                    continue;
                }
                $addressData[$attributeCode] = $attributeData;
            }
        }
        return $addressData;
    }

    /**
     * Retrieve store code
     *
     * @return string
     * @codeCoverageIgnore
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
     */
    protected function getStaticBaseUrl()
    {
        return $this->checkoutSession->getQuote()->getStore()->getBaseUrl(UrlInterface::URL_TYPE_STATIC);
    }

    /**
     * Return quote totals data
     *
     * @return array
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
     *
     * @return array
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
     *
     * @return string
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
     *
     * @return array $paymentMethods
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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

    /**
     * Get notification messages for the quote items
     *
     * @param array $quoteItemData
     * @return array
     */
    private function getQuoteItemsMessages(array $quoteItemData): array
    {
        $quoteItemsMessages = [];
        if ($quoteItemData) {
            foreach ($quoteItemData as $item) {
                $quoteItemsMessages[$item['item_id']] = $item['message'];
            }
        }

        return $quoteItemsMessages;
    }

    /**
     * Get quote address data for checkout
     *
     * @return array
     */
    private function getQuoteAddressData(): array
    {
        $output = [];
        $quote = $this->checkoutSession->getQuote();
        $shippingAddressFromData = [];
        if ($quote->getShippingAddress()->getEmail()) {
            $shippingAddressFromData = $this->getAddressFromData($quote->getShippingAddress());
            if ($shippingAddressFromData) {
                $output['isShippingAddressFromDataValid'] = $quote->getShippingAddress()->validate() === true;
                $output['shippingAddressFromData'] = $shippingAddressFromData;
            }
        }

        if ($quote->getBillingAddress()->getEmail()) {
            $billingAddressFromData = $this->getAddressFromData($quote->getBillingAddress());
            if ($billingAddressFromData && $shippingAddressFromData != $billingAddressFromData) {
                $output['isBillingAddressFromDataValid'] = $quote->getBillingAddress()->validate() === true;
                $output['billingAddressFromData'] = $billingAddressFromData;
            }
        }

        return $output;
    }

    /**
     * Get logged-in customer
     *
     * @return CustomerInterface
     */
    private function getCustomer(): CustomerInterface
    {
        return $this->customerRepository->getById($this->customerSession->getCustomerId());
    }
}
