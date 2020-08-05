<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAdminUi\Controller\Adminhtml\Login;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterface;
use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterfaceFactory;
use Magento\LoginAsCustomerApi\Api\DeleteAuthenticationDataForUserInterface;
use Magento\LoginAsCustomerApi\Api\SaveAuthenticationDataInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Login as customer action
 * Generate secret key and forward to the storefront action
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Login extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_LoginAsCustomer::login';

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

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
     * @var DeleteAuthenticationDataForUserInterface
     */
    private $deleteAuthenticationDataForUser;

    /**
     * @var Url
     */
    private $url;

    /**
     * @param Context $context
     * @param Session $authSession
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param ConfigInterface $config
     * @param AuthenticationDataInterfaceFactory $authenticationDataFactory
     * @param SaveAuthenticationDataInterface $saveAuthenticationData ,
     * @param DeleteAuthenticationDataForUserInterface $deleteAuthenticationDataForUser
     * @param Url $url
     */
    public function __construct(
        Context $context,
        Session $authSession,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository,
        ConfigInterface $config,
        AuthenticationDataInterfaceFactory $authenticationDataFactory,
        SaveAuthenticationDataInterface $saveAuthenticationData,
        DeleteAuthenticationDataForUserInterface $deleteAuthenticationDataForUser,
        Url $url
    ) {
        parent::__construct($context);

        $this->authSession = $authSession;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->config = $config;
        $this->authenticationDataFactory = $authenticationDataFactory;
        $this->saveAuthenticationData = $saveAuthenticationData;
        $this->deleteAuthenticationDataForUser = $deleteAuthenticationDataForUser;
        $this->url = $url;
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
            $this->messageManager->addErrorMessage(__('Login as Customer is disabled.'));
            return $resultRedirect->setPath('customer/index/index');
        }

        $customerId = (int)$this->_request->getParam('customer_id');
        if (!$customerId) {
            $customerId = (int)$this->_request->getParam('entity_id');
        }

        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Customer with this ID are no longer exist.'));
            return $resultRedirect->setPath('customer/index/index');
        }

        if ($this->config->isStoreManualChoiceEnabled()) {
            $storeId = (int)$this->_request->getParam('store_id');
            if (empty($storeId)) {
                $this->messageManager->addNoticeMessage(__('Please select a Store View to login in.'));
                return $resultRedirect->setPath('customer/index/edit', ['id' => $customerId]);
            }
        } else {
            $storeId = (int)$customer->getStoreId();
        }

        $adminUser = $this->authSession->getUser();
        $userId = (int)$adminUser->getId();

        /** @var AuthenticationDataInterface $authenticationData */
        $authenticationData = $this->authenticationDataFactory->create(
            [
                'customerId' => $customerId,
                'adminId' => $userId,
                'extensionAttributes' => null,
            ]
        );

        $this->deleteAuthenticationDataForUser->execute($userId);
        $secret = $this->saveAuthenticationData->execute($authenticationData);
        $this->authSession->setIsLoggedAsCustomer(true);

        $redirectUrl = $this->getLoginProceedRedirectUrl($secret, $storeId);
        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }

    /**
     * Get login proceed redirect url
     *
     * @param string $secret
     * @param int $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    private function getLoginProceedRedirectUrl(string $secret, int $storeId): string
    {
        $store = $this->storeManager->getStore($storeId);

        return $this->url
            ->setScope($store)
            ->getUrl('loginascustomer/login/index', ['secret' => $secret, '_nosid' => true]);
    }
}
