<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Controller\OAuth;

use Magento\AdminAdobeIms\Model\Connection;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Message\ManagerInterface;

class Callback extends Action implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Connection
     */
    private Connection $connection;

    private UrlInterface $backendUrl;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Connection $connection
     * @param UrlInterface $backendUrl
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Connection $connection,
        UrlInterface $backendUrl,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->connection = $connection;
        $this->backendUrl = $backendUrl;
        $this->messageManager = $messageManager;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|ResultInterface|void
     * @throws \Magento\Framework\Exception\AuthorizationException
     */
    public function execute()
    {
        $code = $this->getRequest()->getParam('code');

        /**
         * todo: add error-handling for empty code or invalid/empty accessToken
         */

        $accessToken = $this->connection->getAccessToken($code);
        $profile = $this->connection->getProfile($accessToken);

        /**
         * wip
         */
//        if ($this->userService->exists($profile['email'])) {
            return $this->resultJsonFactory->create()->setData($profile);
//             create session and redirect to 2FA or Admin Dashboard
//        }

//        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
//        $resultRedirect = $this->resultRedirectFactory->create();
//        return $resultRedirect->setPath($this->backendUrl->getRouteUrl('adminhtml'));
    }
}
