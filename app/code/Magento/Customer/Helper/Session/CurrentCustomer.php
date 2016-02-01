<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;

/**
 * Class CurrentCustomer
 */
class CurrentCustomer
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $customerFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $view;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @param CustomerSession $customerSession
     * @param LayoutInterface $layout
     * @param CustomerInterfaceFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param RequestInterface $request
     * @param ModuleManager $moduleManager
     * @param ViewInterface $view
     * @param CustomerRegistry $customerRegistry
     * @param Encryptor $encryptor
     */
    public function __construct(
        CustomerSession $customerSession,
        LayoutInterface $layout,
        CustomerInterfaceFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        RequestInterface $request,
        ModuleManager $moduleManager,
        ViewInterface $view,
        CustomerRegistry $customerRegistry,
        Encryptor $encryptor
    ) {
        $this->customerSession = $customerSession;
        $this->layout = $layout;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->request = $request;
        $this->moduleManager = $moduleManager;
        $this->view = $view;
        $this->customerRegistry = $customerRegistry;
        $this->encryptor = $encryptor;
    }

    /**
     * Returns customer Data with customer group only
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
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
     */
    protected function getCustomerFromService()
    {
        return $this->customerRepository->getById($this->customerSession->getId());
    }

    /**
     * Returns current customer according to session and context
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
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
     */
    public function setCustomerId($customerId)
    {
        $this->customerSession->setId($customerId);
    }

    /**
     * Validate current customer password.
     *
     * @param string $password
     * @return bool true on success
     * @throws InvalidEmailOrPasswordException
     */
    public function validatePassword($password)
    {
        $customer = $this->getCustomerFromService();
        $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
        $hash = $customerSecure->getPasswordHash();
        if (!$this->encryptor->validateHash($password, $hash)) {
            throw new InvalidEmailOrPasswordException(__('The password doesn\'t match this account.'));
        }
        return true;
    }
}
