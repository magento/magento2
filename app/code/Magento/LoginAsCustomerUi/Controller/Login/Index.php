<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerUi\Controller\Login;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\LoginAsCustomerApi\Api\GetAuthenticationDataBySecretInterface;
use Magento\LoginAsCustomerApi\Api\AuthenticateCustomerInterface;
use Psr\Log\LoggerInterface;

/**
 * Login As Customer storefront login action
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
     */
    private $customerRepository;

    /**
     * @var GetAuthenticationDataBySecretInterface
     */
    private $getAuthenticationDataBySecret;

    /**
     * @var AuthenticateCustomerInterface
     */
    private $authenticateCustomer;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ResultFactory $resultFactory
     * @param RequestInterface $request
     * @param CustomerRepositoryInterface $customerRepository
     * @param GetAuthenticationDataBySecretInterface $getAuthenticateDataProcessor
     * @param AuthenticateCustomerInterface $authenticateCustomerProcessor
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResultFactory $resultFactory,
        RequestInterface $request,
        CustomerRepositoryInterface $customerRepository,
        GetAuthenticationDataBySecretInterface $getAuthenticateDataProcessor,
        AuthenticateCustomerInterface $authenticateCustomerProcessor,
        ManagerInterface $messageManager,
        LoggerInterface $logger
    ) {
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->customerRepository = $customerRepository;
        $this->getAuthenticationDataBySecret = $getAuthenticateDataProcessor;
        $this->authenticateCustomer = $authenticateCustomerProcessor;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }

    /**
     * Login As Customer storefront login
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        try {
            $secret = $this->request->getParam('secret');
            if (empty($secret) || !is_string($secret)) {
                throw new LocalizedException(__('Cannot login to account. No secret key provided.'));
            }

            $authenticateData = $this->getAuthenticationDataBySecret->execute($secret);

            try {
                $customer = $this->customerRepository->getById($authenticateData->getCustomerId());
            } catch (NoSuchEntityException $e) {
                throw new LocalizedException(__('Customer are no longer exist.'));
            }

            $this->authenticateCustomer->execute($authenticateData);

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
