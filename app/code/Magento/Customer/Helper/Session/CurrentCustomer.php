<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Helper\Session;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\View\LayoutInterface;

/**
 * Class CurrentCustomer
 * @since 2.0.0
 */
class CurrentCustomer
{
    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     * @since 2.0.0
     */
    protected $layout;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     * @since 2.0.0
     */
    protected $customerFactory;

    /**
     * @var CustomerRepositoryInterface
     * @since 2.0.0
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected $request;

    /**
     * @var \Magento\Framework\Module\Manager
     * @since 2.0.0
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\App\ViewInterface
     * @since 2.0.0
     */
    protected $view;

    /**
     * @param CustomerSession $customerSession
     * @param LayoutInterface $layout
     * @param CustomerInterfaceFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param RequestInterface $request
     * @param ModuleManager $moduleManager
     * @param ViewInterface $view
     * @since 2.0.0
     */
    public function __construct(
        CustomerSession $customerSession,
        LayoutInterface $layout,
        CustomerInterfaceFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        RequestInterface $request,
        ModuleManager $moduleManager,
        ViewInterface $view
    ) {
        $this->customerSession = $customerSession;
        $this->layout = $layout;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->request = $request;
        $this->moduleManager = $moduleManager;
        $this->view = $view;
    }

    /**
     * Returns customer Data with customer group only
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @since 2.0.0
     */
    protected function getDepersonalizedCustomer()
    {
        $customer = $this->customerFactory->create();
        $customer->setGroupId($this->customerSession->getCustomerGroupId());
        return $customer;
    }

    /**
     * Returns customer Data from service
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @since 2.0.0
     */
    protected function getCustomerFromService()
    {
        return $this->customerRepository->getById($this->customerSession->getId());
    }

    /**
     * Returns current customer according to session and context
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @since 2.0.0
     */
    public function getCustomer()
    {
        if ($this->moduleManager->isEnabled('Magento_PageCache')
            && !$this->request->isAjax()
            && $this->view->isLayoutLoaded()
            && $this->layout->isCacheable()
        ) {
            return $this->getDepersonalizedCustomer();
        } else {
            return $this->getCustomerFromService();
        }
    }

    /**
     * Returns customer id from session
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getCustomerId()
    {
        return $this->customerSession->getId();
    }

    /**
     * Set customer id
     *
     * @param int|null $customerId
     * @return void
     * @since 2.0.0
     */
    public function setCustomerId($customerId)
    {
        $this->customerSession->setId($customerId);
    }
}
