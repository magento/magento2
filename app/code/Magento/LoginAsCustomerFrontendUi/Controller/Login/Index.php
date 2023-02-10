<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerFrontendUi\Controller\Login;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\LoginAsCustomerApi\Api\GetAuthenticationDataBySecretInterface;
use Magento\LoginAsCustomerApi\Api\AuthenticateCustomerBySecretInterface;
use Psr\Log\LoggerInterface;

/**
 * Login as Customer storefront login action
 */
class Index implements HttpGetActionInterface
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CustomerRepositoryInterface
     * @deprecated
     */
    private $customerRepository;

    /**
     * @var GetAuthenticationDataBySecretInterface
     * @deprecated
     */
    private $getAuthenticationDataBySecret;

    /**
     * @var AuthenticateCustomerBySecretInterface
     */
    private $authenticateCustomerBySecret;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param ResultFactory $resultFactory
     * @param RequestInterface $request
     * @param CustomerRepositoryInterface $customerRepository
     * @param GetAuthenticationDataBySecretInterface $getAuthenticationDataBySecret
     * @param AuthenticateCustomerBySecretInterface $authenticateCustomerBySecret
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     * @param Session|null $customerSession
     */
    public function __construct(
        ResultFactory $resultFactory,
        RequestInterface $request,
        CustomerRepositoryInterface $customerRepository,
        GetAuthenticationDataBySecretInterface $getAuthenticationDataBySecret,
        AuthenticateCustomerBySecretInterface $authenticateCustomerBySecret,
        ManagerInterface $messageManager,
        LoggerInterface $logger,
        Session $customerSession = null
    ) {
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->customerRepository = $customerRepository;
        $this->getAuthenticationDataBySecret = $getAuthenticationDataBySecret;
        $this->authenticateCustomerBySecret = $authenticateCustomerBySecret;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->customerSession = $customerSession ?? ObjectManager::getInstance()->get(Session::class);
    }

    /**
     * Login as Customer storefront login
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $secret = $this->request->getParam('secret');
        try {
            $this->authenticateCustomerBySecret->execute($secret);
            $customer = $this->customerSession->getCustomer();
            $this->messageManager->addSuccessMessage(
                __('You are logged in as customer: %1', $customer->getFirstname() . ' ' . $customer->getLastname())
            );
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $resultPage->getConfig()->getTitle()->set(__('You are logged in'));
            return $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath('/');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            $this->messageManager->addErrorMessage(__('Cannot login to account.'));
            $resultRedirect->setPath('/');
        }
        return $resultRedirect;
    }
}
