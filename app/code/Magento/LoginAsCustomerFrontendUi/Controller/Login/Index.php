<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerFrontendUi\Controller\Login;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\LoginAsCustomerApi\Api\AuthenticateCustomerBySecretInterface;
use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

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
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @param ResultFactory $resultFactory
     * @param RequestInterface $request
     * @param AuthenticateCustomerBySecretInterface $authenticateCustomerBySecret
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     * @param Session|null $customerSession
     * @param CheckoutSession|null $checkoutSession
     */
    public function __construct(
        ResultFactory $resultFactory,
        RequestInterface $request,
        AuthenticateCustomerBySecretInterface $authenticateCustomerBySecret,
        ManagerInterface $messageManager,
        LoggerInterface $logger,
        ?Session $customerSession = null,
        ?CheckoutSession $checkoutSession = null
    ) {
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->authenticateCustomerBySecret = $authenticateCustomerBySecret;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->customerSession = $customerSession ?? ObjectManager::getInstance()->get(Session::class);
        $this->checkoutSession = $checkoutSession
            ?? ObjectManager::getInstance()->get(CheckoutSession::class);
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
        if ($this->checkoutSession->getQuoteId()) {
            $this->checkoutSession->clearQuote();
            $this->checkoutSession->clearStorage();
        }
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
