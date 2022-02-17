<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Controller\Adminhtml\OAuth;

use Exception;
use Magento\AdminAdobeIms\Exception\AdobeImsOrganizationAuthorizationException;
use Magento\AdminAdobeIms\Exception\AdobeImsTokenAuthorizationException;
use Magento\Backend\App\Action\Context;
use Magento\AdminAdobeIms\Model\ImsConnection;
use Magento\Backend\Controller\Adminhtml\Auth;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;

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
     */
    public function __construct(
        Context $context,
        ImsConnection $imsConnection
    ) {
        parent::__construct($context);
        $this->imsConnection = $imsConnection;
    }

    /**
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $code = $this->getRequest()->getParam('code');
        if ($code === null) {

            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($this->_helper->getHomePageUrl());
            return $resultRedirect;
        }

        try {
            $accessToken = $this->imsConnection->getAccessToken($code);
            $profile = $this->imsConnection->getProfile($accessToken);

            if (false) {

            }

        } catch (AdobeImsTokenAuthorizationException $e) {
            $this->errorMessage(
                'Unable to sign in with the Adobe ID',
                $e->getMessage()
            );
        } catch (AdobeImsOrganizationAuthorizationException $e) {
            $this->errorMessage(
                'You don\'t have access to this Commerce instance',
                $e->getMessage()
            );
        } catch (Exception $e) {
            $this->errorMessage(
                'Error signing in',
                'Something went wrong and we could not sign you in. ' .
                'Please try again or contact your administrator.'
            );
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->_helper->getHomePageUrl());
        return $resultRedirect;
    }

    /**
     * @param string $headline
     * @param string $message
     * @return void
     */
    private function errorMessage(string $headline, string $message): void
    {
        $this->messageManager->addComplexErrorMessage(
            'adminAdobeImsMessage',
            [
                'headline' => __($headline)->getText(),
                'message' => __($message)->getText()
            ]
        );
    }
}
