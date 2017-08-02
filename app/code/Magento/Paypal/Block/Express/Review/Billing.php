<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Paypal Express Onepage checkout block for Billing Address
 */
namespace Magento\Paypal\Block\Express\Review;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Billing extends \Magento\Framework\View\Element\Template
{
    /**
     * Sales Quote Billing Address instance
     *
     * @var \Magento\Quote\Model\Quote\Address
     * @since 2.0.0
     */
    protected $address;

    /**
     * Customer Taxvat Widget block
     *
     * @var \Magento\Customer\Block\Widget\Taxvat
     * @since 2.0.0
     */
    protected $taxvat;

    /**
     * @var \Magento\Quote\Model\Quote\AddressFactory
     * @since 2.0.0
     */
    protected $addressFactory;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     * @since 2.0.0
     */
    protected $customer;

    /**
     * @var Quote
     * @since 2.0.0
     */
    protected $quote;

    /**
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $customerSession;

    /**
     * @var CustomerRepositoryInterface
     * @since 2.0.0
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\App\Http\Context
     * @since 2.0.0
     */
    protected $httpContext;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     * @since 2.0.0
     */
    protected $countryCollectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $resourceSession
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param Quote\AddressFactory $addressFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $resourceSession,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Quote\Model\Quote\AddressFactory $addressFactory,
        array $data = []
    ) {
        $this->addressFactory = $addressFactory;
        $this->_isScopePrivate = true;
        $this->httpContext = $httpContext;
        $this->customerRepository = $customerRepository;
        $this->checkoutSession = $resourceSession;
        $this->customerSession = $customerSession;
        $this->countryCollectionFactory = $countryCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Initialize billing address step
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->getCheckout()->setStepData(
            'billing',
            ['label' => __('Billing Information'), 'is_show' => true]
        );

        if ($this->isCustomerLoggedIn()) {
            $this->getCheckout()->setStepData('billing', 'allow', true);
        }
        parent::_construct();
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isUseBillingAddressForShipping()
    {
        if ($this->getQuote()->getIsVirtual() || !$this->getQuote()->getShippingAddress()->getSameAsBilling()) {
            return false;
        }
        return true;
    }

    /**
     * Return country collection
     *
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     * @since 2.0.0
     */
    public function getCountries()
    {
        return $this->countryCollectionFactory->create()->loadByStore();
    }

    /**
     * Return checkout method
     *
     * @return string
     * @since 2.0.0
     */
    public function getMethod()
    {
        return $this->getQuote()->getCheckoutMethod();
    }

    /**
     * Return Customer Address First Name
     * If Sales Quote Address First Name is not defined - return Customer First Name
     *
     * @return string
     * @since 2.0.0
     */
    public function getFirstname()
    {
        return $this->getAddress()->getFirstname();
    }

    /**
     * Return Customer Address Last Name
     * If Sales Quote Address Last Name is not defined - return Customer Last Name
     *
     * @return string
     * @since 2.0.0
     */
    public function getLastname()
    {
        return $this->getAddress()->getLastname();
    }

    /**
     * Check is Quote items can ship to
     *
     * @return bool
     * @since 2.0.0
     */
    public function canShip()
    {
        return !$this->getQuote()->isVirtual();
    }

    /**
     * @return void
     * @since 2.0.0
     */
    public function getSaveUrl()
    {
    }

    /**
     * Get Customer Taxvat Widget block
     *
     * @return \Magento\Customer\Block\Widget\Taxvat
     * @since 2.0.0
     */
    protected function _getTaxvat()
    {
        if (!$this->taxvat) {
            $this->taxvat = $this->getLayout()->createBlock(\Magento\Customer\Block\Widget\Taxvat::class);
        }

        return $this->taxvat;
    }

    /**
     * Check whether taxvat is enabled
     *
     * @return bool
     * @since 2.0.0
     */
    public function isTaxvatEnabled()
    {
        return $this->_getTaxvat()->isEnabled();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getTaxvatHtml()
    {
        return $this->_getTaxvat()
            ->setTaxvat($this->getQuote()->getCustomerTaxvat())
            ->setFieldIdFormat('billing:%s')
            ->setFieldNameFormat('billing[%s]')
            ->toHtml();
    }

    /**
     * Return Sales Quote Address model
     *
     * @return \Magento\Quote\Model\Quote\Address
     * @since 2.0.0
     */
    public function getAddress()
    {
        if ($this->address === null) {
            if ($this->isCustomerLoggedIn() || $this->getQuote()->getBillingAddress()) {
                $this->address = $this->getQuote()->getBillingAddress();
                if (!$this->address->getFirstname()) {
                    $this->address->setFirstname($this->getQuote()->getCustomer()->getFirstname());
                }
                if (!$this->address->getLastname()) {
                    $this->address->setLastname($this->getQuote()->getCustomer()->getLastname());
                }
            } else {
                $this->address = $this->addressFactory->create();
            }
        }

        return $this->address;
    }

    /**
     * Get config
     *
     * @param string $path
     * @return string|null
     * @since 2.0.0
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get logged in customer
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @since 2.0.0
     */
    protected function _getCustomer()
    {
        if (empty($this->customer)) {
            $this->customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
        }
        return $this->customer;
    }

    /**
     * Retrieve checkout session model
     *
     * @return \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    public function getCheckout()
    {
        return $this->checkoutSession;
    }

    /**
     * Retrieve sales quote model
     *
     * @return Quote
     * @since 2.0.0
     */
    public function getQuote()
    {
        if (empty($this->quote)) {
            $this->quote = $this->getCheckout()->getQuote();
        }
        return $this->quote;
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isCustomerLoggedIn()
    {
        return $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }
}
