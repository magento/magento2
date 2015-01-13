<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Account;

use Magento\Customer\Api\CustomerRepositoryInterface;

class Customer extends \Magento\Framework\View\Element\Template
{
    /** @var CustomerRepositoryInterface */
    protected $customerRepository;

    /** @var \Magento\Customer\Helper\View */
    protected $_viewHelper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CustomerRepositoryInterface $accountManagement
     * @param \Magento\Customer\Helper\View $viewHelper
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CustomerRepositoryInterface $accountManagement,
        \Magento\Customer\Helper\View $viewHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerRepository = $accountManagement;
        $this->_viewHelper = $viewHelper;
        $this->httpContext = $httpContext;
        $this->currentCustomer = $currentCustomer;
        $this->_isScopePrivate = true;
    }

    /**
     * Checking customer login status
     *
     * @return bool
     */
    public function customerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * Return the full name of the customer currently logged in
     *
     * @return string|null
     */
    public function getCustomerName()
    {
        try {
            $customer = $this->customerRepository->getById($this->currentCustomer->getCustomerId());
            return $this->escapeHtml($this->_viewHelper->getCustomerName($customer));
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }
}
