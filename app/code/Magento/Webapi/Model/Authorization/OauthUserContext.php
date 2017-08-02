<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Oauth\Helper\Request as OauthRequestHelper;
use Magento\Framework\Oauth\OauthInterface as OauthService;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Framework\Webapi\Request;

/**
 * A user context determined by OAuth headers in a HTTP request.
 * @since 2.0.0
 */
class OauthUserContext implements UserContextInterface
{
    /**
     * @var Request
     * @since 2.0.0
     */
    protected $request;

    /**
     * @var IntegrationServiceInterface
     * @since 2.0.0
     */
    protected $integrationService;

    /**
     * @var OauthService
     * @since 2.0.0
     */
    protected $oauthService;

    /**
     * @var  OauthRequestHelper
     * @since 2.0.0
     */
    protected $oauthHelper;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $integrationId;

    /**
     * Initialize dependencies.
     *
     * @param Request $request
     * @param IntegrationServiceInterface $integrationService
     * @param OauthService $oauthService
     * @param OauthRequestHelper $oauthHelper
     * @since 2.0.0
     */
    public function __construct(
        Request $request,
        IntegrationServiceInterface $integrationService,
        OauthService $oauthService,
        OauthRequestHelper $oauthHelper
    ) {
        $this->request = $request;
        $this->integrationService = $integrationService;
        $this->oauthService = $oauthService;
        $this->oauthHelper = $oauthHelper;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getUserId()
    {
        if ($this->integrationId) {
            return $this->integrationId;
        }
        $oauthRequest = $this->oauthHelper->prepareRequest($this->request);
        //If its not a valid Oauth request no further processing is needed
        if (empty($oauthRequest)) {
            return null;
        }
        $consumerId = $this->oauthService->validateAccessTokenRequest(
            $oauthRequest,
            $this->oauthHelper->getRequestUrl($this->request),
            $this->request->getMethod()
        );
        $integration = $this->integrationService->findActiveIntegrationByConsumerId($consumerId);
        return $this->integrationId = ($integration->getId() ? (int)$integration->getId() : null);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getUserType()
    {
        return UserContextInterface::USER_TYPE_INTEGRATION;
    }
}
