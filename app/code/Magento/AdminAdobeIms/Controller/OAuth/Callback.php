<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Controller\OAuth;

use Magento\AdminAdobeIms\Model\Connection;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

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

        $accessToken = $this->connection->getAccessToken($code);

        $profile = $this->connection->getProfile($accessToken);

        /**
         * wip
         */
//        if ($this->userService->exists($profile['email'])) {
            // create session and redirect to 2FA or Admin Dashboard
//        }

        //user doesn't exists, redirect to admin login and show error.

        return $this->resultJsonFactory->create()->setData($profile);
    }
}
