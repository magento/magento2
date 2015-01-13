<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Controller\Token;

use Magento\Integration\Model\Integration as IntegrationModel;
use Magento\Integration\Service\V1\IntegrationInterface as IntegrationService;
use Magento\Integration\Service\V1\OauthInterface as IntegrationOauthService;

class Access extends \Magento\Framework\App\Action\Action
{
    /**
     * @var  \Magento\Framework\Oauth\OauthInterface
     */
    protected $_oauthService;

    /**
     * @var  IntegrationOauthService
     */
    protected $_intOauthService;

    /**
     * @var  IntegrationService
     */
    protected $_integrationService;

    /**
     * @var  \Magento\Framework\Oauth\Helper\Request
     */
    protected $_helper;

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
        $this->_oauthService = $oauthService;
        $this->_intOauthService = $intOauthService;
        $this->_integrationService = $integrationService;
        $this->_helper = $helper;
    }

    /**
     * Initiate AccessToken request operation
     *
     * @return void
     */
    public function execute()
    {
        try {
            $requestUrl = $this->_helper->getRequestUrl($this->getRequest());
            $request = $this->_helper->prepareRequest($this->getRequest(), $requestUrl);

            // Request access token in exchange of a pre-authorized token
            $response = $this->_oauthService->getAccessToken($request, $requestUrl, $this->getRequest()->getMethod());
            //After sending the access token, update the integration status to active;
            $consumer = $this->_intOauthService->loadConsumerByKey($request['oauth_consumer_key']);
            $this->_integrationService->findByConsumerId(
                $consumer->getId()
            )->setStatus(
                IntegrationModel::STATUS_ACTIVE
            )->save();
        } catch (\Exception $exception) {
            $response = $this->_helper->prepareErrorResponse($exception, $this->getResponse());
        }
        $this->getResponse()->setBody(http_build_query($response));
    }
}
