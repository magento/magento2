<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Controller\Adminhtml\OAuth;

use Magento\Backend\App\Action\Context;
use Magento\AdminAdobeIms\Model\Connection;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

class Callback extends \Magento\Backend\Controller\Adminhtml\Auth implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Connection
     */
    private Connection $connection;

    /**
     * @var UrlInterface
     */
    private UrlInterface $backendUrl;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Connection $connection
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Connection $connection
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->connection = $connection;
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

//        $accessToken = $this->connection->getAccessToken($code);
//        $profile = $this->connection->getProfile($accessToken);

        try {
            if (false) {

            }

            throw new AuthenticationException(__('No user found.'));

        } catch (AuthenticationException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath($this->_helper->getHomePageUrl());
    }
}
