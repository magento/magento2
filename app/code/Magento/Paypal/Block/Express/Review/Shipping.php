<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Paypal Express Onepage checkout block for Shipping Address
 */
namespace Magento\Paypal\Block\Express\Review;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Class \Magento\Paypal\Block\Express\Review\Shipping
 *
 * @since 2.0.0
 */
class Shipping extends \Magento\Framework\View\Element\Template
{
    /**
     * Sales Quote Shipping Address instance
     *
     * @var \Magento\Quote\Model\Quote\Address
     * @since 2.0.0
     */
    protected $address = null;

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
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $customerSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $resourceSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Quote\Model\Quote\AddressFactory $addressFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $resourceSession,
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
        parent::__construct($context, $data);
    }

    /**
     * Initialize shipping address step
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->checkoutSession->setStepData(
            'shipping',
            ['label' => __('Shipping Information'), 'is_show' => $this->isShow()]
        );

        parent::_construct();
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
     * Retrieve is allow and show block
     *
     * @return bool
     * @since 2.0.0
     */
    public function isShow()
    {
        return !$this->getQuote()->isVirtual();
    }

    /**
     * Return Sales Quote Address model (shipping address)
     *
     * @return \Magento\Quote\Model\Quote\Address
     * @since 2.0.0
     */
    public function getAddress()
    {
        if ($this->address === null) {
            if ($this->isCustomerLoggedIn() || $this->getQuote()->getShippingAddress()) {
                $this->address = $this->getQuote()->getShippingAddress();
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
     * Retrieve sales quote model
     *
     * @return Quote
     * @since 2.0.0
     */
    public function getQuote()
    {
        if (empty($this->quote)) {
            $this->quote = $this->checkoutSession->getQuote();
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
