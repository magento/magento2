<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Controller\Adminhtml\OAuth;

use Exception;
use Magento\AdminAdobeIms\Logger\AdminAdobeImsLogger;
use Magento\AdminAdobeIms\Model\Authorization\AdobeImsAdminTokenUserService;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Controller\Adminhtml\Auth;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class ImsReauthCallback extends Auth implements HttpGetActionInterface
{
    public const ACTION_NAME = 'imsreauthcallback';

    /**
     * Constants of response
     *
     * RESPONSE_TEMPLATE - template of response
     * RESPONSE_SUCCESS_CODE success code
     * RESPONSE_ERROR_CODE error code
     */
    private const RESPONSE_TEMPLATE = 'auth[code=%s;message=%s]';
    private const RESPONSE_SUCCESS_CODE = 'success';
    private const RESPONSE_ERROR_CODE = 'error';

    /**
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @var AdminAdobeImsLogger
     */
    private AdminAdobeImsLogger $logger;

    /**
     * @var AdobeImsAdminTokenUserService
     */
    private AdobeImsAdminTokenUserService $adminTokenUserService;

    /**
     * @param Context $context
     * @param ImsConfig $adminImsConfig
     * @param AdobeImsAdminTokenUserService $adminTokenUserService
     * @param AdminAdobeImsLogger $logger
     */
    public function __construct(
        Context                         $context,
        ImsConfig                       $adminImsConfig,
        AdobeImsAdminTokenUserService $adminTokenUserService,
        AdminAdobeImsLogger             $logger
    ) {
        parent::__construct($context);
        $this->adminImsConfig = $adminImsConfig;
        $this->adminTokenUserService = $adminTokenUserService;
        $this->logger = $logger;
    }

    /**
     * Execute AdobeIMS callback
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        if (!$this->adminImsConfig->enabled()) {
            $this->getMessageManager()->addErrorMessage('Adobe Sign-In is disabled.');

            $response = sprintf(
                self::RESPONSE_TEMPLATE,
                self::RESPONSE_ERROR_CODE,
                __('Adobe Sign-In is disabled.')
            );

            $resultRaw->setContents($response);

            return $resultRaw;
        }

        try {
            $this->adminTokenUserService->processLoginRequest(true);

            $response = sprintf(
                self::RESPONSE_TEMPLATE,
                self::RESPONSE_SUCCESS_CODE,
                __('Authorization was successful')
            );
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            $response = sprintf(
                self::RESPONSE_TEMPLATE,
                self::RESPONSE_ERROR_CODE,
                $e->getMessage()
            );
        }

        $resultRaw->setContents($response);

        return $resultRaw;
    }
}
