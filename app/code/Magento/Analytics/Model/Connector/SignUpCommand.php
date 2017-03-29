<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\IntegrationManager;

/**
 * Class SignUpCommand
 *
 * SignUp merchant for Free Tier project
 */
class SignUpCommand implements CommandInterface
{
    /**
     * @var AnalyticsToken
     */
    private $analyticsToken;

    /**
     * @var IntegrationManager
     */
    private $integrationManager;

    /**
     * @var SignUpRequest
     */
    private $signUpRequest;

    /**
     * SignUpCommand constructor.
     *
     * @param SignUpRequest $signUpRequest
     * @param AnalyticsToken $analyticsToken
     * @param IntegrationManager $integrationManager
     */
    public function __construct(
        SignUpRequest $signUpRequest,
        AnalyticsToken $analyticsToken,
        IntegrationManager $integrationManager
    ) {
        $this->analyticsToken = $analyticsToken;
        $this->integrationManager = $integrationManager;
        $this->signUpRequest = $signUpRequest;
    }

    /**
     * Executes signUp command
     *
     * During this call Magento generates or retrieves access token for the integration user
     * In case successful generation Magento activates user and sends access token to MA
     * As the response, Magento receives a token to MA
     * Magento stores this token in System Configuration
     *
     * This method returns true in case of success
     *
     * @return bool
     */
    public function execute()
    {
        $integrationToken = $this->integrationManager->generateToken();
        if ($integrationToken) {
            $this->integrationManager->activateIntegration();
            $responseToken = $this->signUpRequest->call($integrationToken->getToken());
            if ($responseToken) {
                $this->analyticsToken->storeToken($responseToken);
            }
        }
        return ((bool)$integrationToken && isset($responseToken) && (bool)$responseToken);
    }
}
