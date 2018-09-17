<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Controller\Token;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Integration\Model\Integration as IntegrationModel;
use Magento\Integration\Api\IntegrationServiceInterface as IntegrationService;
use Magento\Integration\Api\OauthServiceInterface as IntegrationOauthService;
use Magento\Framework\App\Action\Action;

class Access extends Action implements CsrfAwareActionInterface
{
    /**
     * @var \Magento\Framework\Oauth\OauthInterface
     */
    protected $oauthService;

    /**
     * @var IntegrationOauthService
     */
    protected $intOauthService;

    /**
     * @var IntegrationService
     */
    protected $integrationService;

    /**
     * @var \Magento\Framework\Oauth\Helper\Request
     */
    protected $helper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Oauth\OauthInterface $oauthService
     * @param IntegrationOauthService $intOauthService
     * @param IntegrationService $integrationService
     * @param \Magento\Framework\Oauth\Helper\Request $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Oauth\OauthInterface $oauthService,
        IntegrationOauthService $intOauthService,
        IntegrationService $integrationService,
        \Magento\Framework\Oauth\Helper\Request $helper
    ) {
        parent::__construct($context);
        $this->oauthService = $oauthService;
        $this->intOauthService = $intOauthService;
        $this->integrationService = $integrationService;
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Initiate AccessToken request operation
     *
     * @return void
     */
    public function execute()
    {
        try {
            $requestUrl = $this->helper->getRequestUrl($this->getRequest());
            $request = $this->helper->prepareRequest($this->getRequest(), $requestUrl);

            // Request access token in exchange of a pre-authorized token
            $response = $this->oauthService->getAccessToken($request, $requestUrl, $this->getRequest()->getMethod());
            //After sending the access token, update the integration status to active;
            $consumer = $this->intOauthService->loadConsumerByKey($request['oauth_consumer_key']);
            $integration = $this->integrationService->findByConsumerId($consumer->getId());
            $integration->setStatus(IntegrationModel::STATUS_ACTIVE);
            $integration->save();
        } catch (\Exception $exception) {
            $response = $this->helper->prepareErrorResponse($exception, $this->getResponse());
        }
        $this->getResponse()->setBody(http_build_query($response));
    }
}
