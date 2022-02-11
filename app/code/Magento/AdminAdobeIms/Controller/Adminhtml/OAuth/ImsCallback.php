<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Controller\Adminhtml\OAuth;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\AdminAdobeIms\Model\ImsConnection;
use Magento\Backend\Controller\Adminhtml\Auth;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Message\ManagerInterface;

class ImsCallback extends Auth implements HttpGetActionInterface
{
    public const ACTION_NAME = 'imscallback';

    /**
     * @var ImsConnection
     */
    private ImsConnection $imsConnection;

    /**
     * @param Context $context
     * @param ImsConnection $imsConnection
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        ImsConnection $imsConnection,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->imsConnection = $imsConnection;
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

        try {
            $accessToken = $this->imsConnection->getAccessToken($code);
            $profile = $this->imsConnection->getProfile($accessToken);

            if (false) {

            }

        } catch (AuthenticationException $e) {
            $this->messageManager->addComplexErrorMessage('adminAdobeImsMessage',['message' => $e->getMessage()]);
        } catch (Exception $e) {
            $this->messageManager->addComplexErrorMessage(
                'adminAdobeImsMessage',
                [
                    'headline' => __('Error signing in')->getText(),
                    'message' => __('Something went wrong and we could not sign you in. ' .
                        'Please try again or contact your administrator.')->getText()
                ]
            );
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath($this->_helper->getHomePageUrl());
    }
}
