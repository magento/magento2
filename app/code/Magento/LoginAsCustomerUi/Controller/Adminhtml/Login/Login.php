<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerUi\Controller\Adminhtml\Login;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\LoginAsCustomer\Api\ConfigInterface;
use Magento\LoginAsCustomer\Api\Data\AuthenticationDataInterface;
use Magento\LoginAsCustomer\Api\Data\AuthenticationDataInterfaceFactor;
use Magento\LoginAsCustomer\Api\SaveAuthenticationDataInterface;

/**
 * Login as customer action
 * Generate secret key and forward to the storefront action
 *
 * This action can be executed via GET request when "Store View To Login In" is disabled, and POST when it is enabled
 */
class Login implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_LoginAsCustomerUi::login_button';

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Url
     */
    private $url;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var AuthenticationDataInterfaceFactory
     */
    private $authenticationDataFactory;

    /**
     * @var SaveAuthenticationDataInterface
     */
    private $saveAuthenticationData;

    /**
     * @param ResultFactory $resultFactory
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Url $url
     * @param CustomerRepositoryInterface $customerRepository
     * @param ConfigInterface $config
     * @param AuthenticationDataInterfaceFactory $authenticationDataFactory
     * @param SaveAuthenticationDataInterface $saveAuthenticationData
     */
    public function __construct(
        ResultFactory $resultFactory,
        RequestInterface $request,
        ManagerInterface $messageManager,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Url $url,
        CustomerRepositoryInterface $customerRepository,
        ConfigInterface $config,
        AuthenticationDataInterfaceFactory $authenticationDataFactory,
        SaveAuthenticationDataInterface $saveAuthenticationData
    ) {
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->authSession = $authSession;
        $this->storeManager = $storeManager;
        $this->url = $url;
        $this->customerRepository = $customerRepository;
        $this->config = $config;
        $this->authenticationDataFactory = $authenticationDataFactory;
        $this->saveAuthenticationData = $saveAuthenticationData;
    }

    /**
     * Login as customer
     *
     * @return ResultInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute(): ResultInterface
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!$this->config->isEnabled()) {
            $this->messageManager->addErrorMessage(__('Login As Customer is disabled.'));
            return $resultRedirect->setPath('customer/index/index');
        }

        $customerId = (int)$this->request->getParam('customer_id');
        if (!$customerId) {
            $customerId = (int)$this->request->getParam('entity_id');
        }

        try {
            $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Customer with this ID are no longer exist.'));
            return $resultRedirect->setPath('customer/index/index');
        }

        $storeId = $this->request->getParam('store_id');
        if (empty($storeId) && $this->config->isStoreManualChoiceEnabled()) {
            $this->messageManager->addNoticeMessage(__('Please select a Store View to login in.'));
            return $resultRedirect->setPath('loginascustomer/login/manual', ['customer_id' => $customerId]);
        }

        $adminUser = $this->authSession->getUser();

        /** @var AuthenticationDataInterface $authenticationData */
        $authenticationData = $this->authenticationDataFactory->create(
            [
                'customerId' => $customerId,
                'adminId' => (int)$adminUser->getId(),
                'extensionAttributes' => null,
            ]
        );
        $secret = $this->saveAuthenticationData->execute($authenticationData);

        $redirectUrl = $this->getLoginProceedRedirectUrl($secret, $storeId);
        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }

    /**
     * @param string $secret
     * @param int|null $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    private function getLoginProceedRedirectUrl(string $secret, ?int $storeId): string
    {
        if (null === $storeId) {
            $store = $this->storeManager->getDefaultStoreView();
        } else {
            $store = $this->storeManager->getStore($storeId);
        }

        $redirectUrl = $this->url
            ->setScope($store)
            ->getUrl('loginascustomer/login/index', ['secret' => $secret, '_nosid' => true]);
        return $redirectUrl;
    }
}
