<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Onepage checkout block
 */
class Checkout extends \Magento\Checkout\Block\Onepage\AbstractOnepage
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var bool
     */
    protected $_isScopePrivate = false;

    /**
     * @var array
     */
    protected $jsLayout;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $cartRepository;

    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     */
    protected $cartItemRepository;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface
     */
    protected $cartData;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var \Magento\Quote\Api\ShippingMethodManagementInterface
     */
    protected $shippingMethodManagement;

    /**
     * @var \Magento\Quote\Model\Quote\AddressDataProvider
     */
    protected $addressDataProvider;

    /**
     * Checkout data
     *
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutData;

    /**
     * @var \Magento\Customer\Model\Registration
     */
    protected $registration;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $resourceSession
     * @param \Magento\Directory\Model\Resource\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Model\Resource\Region\CollectionFactory $regionCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressConfig $addressConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Quote\Model\QuoteRepository $cartRepositoryInterface
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagement
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $cartItemRepository
     * @param \Magento\Quote\Model\Quote\AddressDataProvider $addressDataProvider
     * @param \Magento\Checkout\Helper\Data $checkoutData
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Customer\Model\Registration $registration
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $resourceSession,
        \Magento\Directory\Model\Resource\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\Resource\Region\CollectionFactory $regionCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        AddressConfig $addressConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Quote\Model\QuoteRepository $cartRepositoryInterface,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagement,
        \Magento\Quote\Api\CartItemRepositoryInterface $cartItemRepository,
        \Magento\Quote\Model\Quote\AddressDataProvider $addressDataProvider,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Customer\Model\Registration $registration,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $directoryHelper,
            $configCacheType,
            $customerSession,
            $resourceSession,
            $countryCollectionFactory,
            $regionCollectionFactory,
            $customerRepository,
            $addressConfig,
            $httpContext,
            $addressMapper,
            $data
        );
        $this->formKey = $formKey;
        $this->_isScopePrivate = true;
        $this->jsLayout = is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->cartRepository = $cartRepositoryInterface;
        $this->localeCurrency = $localeCurrency;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->cartItemRepository = $cartItemRepository;
        $this->addressDataProvider = $addressDataProvider;
        $this->checkoutData = $checkoutData;
        $this->registration = $registration;
        $this->customerUrl = $customerUrl;
    }


    /**
     * @return string
     */
    public function getJsLayout()
    {
        if (isset($this->jsLayout['components']['checkout']['children']['steps']['children']['billingAddress']
            ['children']['billing-address-fieldset']['children']
        )) {
            $fields = $this->jsLayout['components']['checkout']['children']['steps']['children']['billingAddress']
                ['children']['billing-address-fieldset']['children'];
            $this->jsLayout['components']['checkout']['children']['steps']['children']['billingAddress']
                ['children']['billing-address-fieldset']['children'] = $this->addressDataProvider
                    ->getAdditionalAddressFields('billingAddressProvider', 'billingAddress', $fields);
        }
        return \Zend_Json::encode($this->jsLayout);
    }



    /**
     * Get 'one step checkout' step data
     *
     * @return array
     */
    public function getSteps()
    {
        $steps = array();
        $stepCodes = $this->_getStepCodes();

        if ($this->isCustomerLoggedIn()) {
            $stepCodes = array_diff($stepCodes, array('login'));
        }

        foreach ($stepCodes as $step) {
            $steps[$step] = $this->getCheckout()->getStepData($step);
        }

        return $steps;
    }

    /**
     * Get active step
     *
     * @return string
     */
    public function getActiveStep()
    {
        return $this->isCustomerLoggedIn() ? 'billing' : 'login';
    }

    /**
     * Retrieve form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Retrieve current customer data.
     *
     * @return string
     */
    public function getCustomerData()
    {
        if ($this->isCustomerLoggedIn()) {
            return \Zend_Json::encode($this->_getCustomer()->__toArray());
        }
        return \Zend_Json::encode([]);
    }

    /**
     * Retrieve current active quote object.
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    protected function getCartData()
    {
        if (!$this->cartData && $this->getQuote()->getId()) {
            $this->cartData = $this->cartRepository->get($this->getQuote()->getId());
            if (!$this->cartData->getCustomer()->getId()) {
                $this->cartRepository->save($this->getQuote()->setCheckoutMethod('guest'));
            } else {
                $this->cartRepository->save($this->getQuote()->setCheckoutMethod(null));
            }
        }
        return $this->cartData;
    }

    /**
     * Retrieve current active quote.
     *
     * @return string
     */
    public function getCart()
    {
        return \Zend_Json::encode($this->getCartData());
    }

    /**
     * Cart items as array
     *
     * @return array
     */
    public function getCartItems()
    {
        $itemData = [];
        if ($this->getQuote()->getId()) {
            $itemObjects = $this->cartItemRepository->getList($this->getQuote()->getId());
            /** @var \Magento\Quote\Api\Data\CartItemInterface $item */
            foreach($itemObjects as $item) {
                $itemData[] = $item->toArray();
            }
        }
        return \Zend_Json::encode($itemData);
    }

    /**
     * Retrieve active quote currency code.
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        $symbol = '';
        if ($this->getCartData()) {
            $currencyCode = $this->getCartData()->getQuoteCurrencyCode();
            $currency = $this->localeCurrency->getCurrency($currencyCode);
            $symbol = $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();
        }
        return \Zend_Json::encode(['data' => $symbol]);
    }

    /**
     * Retrieve selected shipping method.
     *
     * @return string|bool
     */
    public function getSelectedShippingMethod()
    {
        $selectedShippingMethod = false;
        $quoteId = $this->getQuote()->getId();
        try {
            $shippingMethod = $this->shippingMethodManagement->get($quoteId);
            if ($shippingMethod) {
                $selectedShippingMethod = $shippingMethod->getCarrierCode() . "_" . $shippingMethod->getMethodCode();
            }
        } catch( \Exception $e) {
            //do nothing
        }
        return \Zend_Json::encode($selectedShippingMethod);
    }

    /**
     *  Retrieve quote store code
     *  @return string
     */
    public function getStoreCode()
    {
        return \Zend_Json::encode($this->getQuote()->getStore()->getCode());
    }

    /**
     * Check if guests checkout is allowed
     *
     * @return string
     */
    public function isAllowedGuestCheckout()
    {
        return \Zend_Json::encode($this->checkoutData->isAllowedGuestCheckout($this->getQuote()));
    }

    /**
     * Check if registration is allowed
     *
     * @return string
     */
    public function isRegistrationAllowed()
    {
        return \Zend_Json::encode($this->registration->isAllowed());
    }

    /**
     * Return true if checkout method register
     *
     * @return string
     */
    public function isMethodRegister()
    {
        return \Zend_Json::encode(
            $this->getQuote()->getCheckoutMethod() == \Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER
        );
    }

    /**
     * Check if user must be logged during checkout process
     *
     * @return string
     */
    public function  isCustomerMustBeLogged()
    {
        return \Zend_Json::encode($this->checkoutData->isCustomerMustBeLogged());
    }

    /**
     * Return registration URL
     *
     * @return string
     */
    public function getRegisterUrl()
    {
        return \Zend_Json::encode($this->customerUrl->getRegisterUrl());
    }

    /**
     * @return int
     */
    public function customerHasAddresses()
    {
        try {
            return \Zend_Json::encode(count($this->_getCustomer()->getAddresses()));
        } catch (NoSuchEntityException $e) {
            return \Zend_Json::encode(0);
        }
    }
}
