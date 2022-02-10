<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Controller\Adminhtml\OAuth;

use Magento\Backend\App\Action\Context;
use Magento\AdminAdobeIms\Model\Connection;
use Magento\Backend\Controller\Adminhtml\Auth;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Message\ManagerInterface;

class Callback extends Auth implements HttpGetActionInterface
{
    /**
     * @var Connection
     */
    private Connection $connection;

    /**
     * @param Context $context
     * @param Connection $connection
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        Connection $connection,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->connection = $connection;
        $this->messageManager = $messageManager;
    }

    /**
     * @return Redirect
     * @throws AuthorizationException
     */
    public function execute(): Redirect
    {
        $code = $this->getRequest()->getParam('code');

        /**
         * todo: add error-handling for empty code or invalid/empty accessToken
         */

        $accessToken = $this->connection->getAccessToken($code);
        $profile = $this->connection->getProfile($accessToken);

        try {
            if (false) {

            }

            throw new AuthenticationException(__('No user found.'));

        } catch (AuthenticationException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath($this->_helper->getHomePageUrl());
    }
}
