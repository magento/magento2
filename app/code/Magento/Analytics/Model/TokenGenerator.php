<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model;

use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Model\Integration;

/**
 * Class TokenGenerator
 */
class TokenGenerator
{
    /**
     * @var IntegrationServiceInterface
     */
    private $integrationService;

    /**
     * @var OauthServiceInterface
     */
    private $oauthService;
    /**
     * @var MagentoAnalyticsApiUser
     */
    private $analyticsApiUser;

    /**
     * GenerateTokenCommand constructor.
     * @param IntegrationServiceInterface $integrationService
     * @param OauthServiceInterface $oauthService
     * @param MagentoAnalyticsApiUser $analyticsApiUser
     */
    public function __construct(
        IntegrationServiceInterface $integrationService,
        OauthServiceInterface $oauthService,
        MagentoAnalyticsApiUser $analyticsApiUser
    ) {
        $this->integrationService = $integrationService;
        $this->oauthService = $oauthService;
        $this->analyticsApiUser = $analyticsApiUser;
    }

    /**
     * This method execute Generate Token command and enable integration
     * @return bool
     */
    public function execute()
    {
        $creationResult = $this->oauthService->createAccessToken($this->analyticsApiUser->getConsumerId(), true);
        if ($creationResult === true) {
            $integrationData = $this->analyticsApiUser->getData();
            $integrationData['status'] = Integration::STATUS_ACTIVE;
            $this->integrationService->update($integrationData);
            return true;
        }
        return false;
    }
}
