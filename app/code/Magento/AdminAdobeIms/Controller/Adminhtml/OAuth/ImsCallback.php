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
use Magento\AdminAdobeIms\Service\AdminLoginProcessService;
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
     * @var AdminLoginProcessService
     */
    private AdminLoginProcessService $adminLoginProcessService;

    /**
     * @param Context $context
     * @param ImsConnection $imsConnection
     * @param ManagerInterface $messageManager
     * @param AdminLoginProcessService $adminLoginProcessService
     */
    public function __construct(
        Context          $context,
        ImsConnection    $imsConnection,
        ManagerInterface $messageManager,
        AdminLoginProcessService $adminLoginProcessService
    )
    {
        parent::__construct($context);
        $this->imsConnection = $imsConnection;
        $this->messageManager = $messageManager;
        $this->adminLoginProcessService = $adminLoginProcessService;
    }

    /**
     * @return Redirect
     * @throws AuthorizationException
     */
    public function execute(): Redirect
    {
        try {
            $code = $this->getRequest()->getParam('code');

            if (is_null($code)) {
                throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
            }

            $tokenResponse = $this->imsConnection->getTokenResponse($code);

            $profile = $this->imsConnection->getProfile($tokenResponse->getAccessToken());
            if (empty($profile['email'])) {
                throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
            }
            $this->adminLoginProcessService->execute($profile, $tokenResponse);
        } catch (AdobeImsTokenAuthorizationException $e) {
            $this->imsErrorMessage(
                'Unable to sign in with the Adobe ID',
                $e->getMessage()
            );
        } catch (AdobeImsOrganizationAuthorizationException $e) {
            $this->imsErrorMessage(
                'You don\'t have access to this Commerce instance',
                $e->getMessage()
            );
        } catch (Exception $e) {
            $this->imsErrorMessage(
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
    private function imsErrorMessage(string $headline, string $message): void
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
