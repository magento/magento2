<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Controller\Login;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\LoginAsCustomer\Model\Login;
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
     * @var Login
     */
    private $loginModel;

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
     * @param Login $loginModel
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResultFactory $resultFactory,
        RequestInterface $request,
        Login $loginModel,
        ManagerInterface $messageManager,
        LoggerInterface $logger
    ) {
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->loginModel = $loginModel;
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
            $login = $this->initLogin();
            $login->authenticateCustomer();

            $this->messageManager->addSuccessMessage(
                __('You are logged in as customer: %1', $login->getCustomer()->getName())
            );
            $resultRedirect->setPath('*/*/proceed');

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

    /**
     * Init login info
     *
     * @return Login
     * @throws LocalizedException
     */
    private function initLogin(): Login
    {
        $secret = $this->request->getParam('secret');
        if (!$secret) {
            throw new LocalizedException(__('Cannot login to account. No secret key provided.'));
        }

        $login = $this->loginModel->loadNotUsed($secret);

        if ($login->getId()) {
            return $login;
        } else {
            throw new LocalizedException(__('Cannot login to account. Secret key is not valid'));
        }
    }
}
