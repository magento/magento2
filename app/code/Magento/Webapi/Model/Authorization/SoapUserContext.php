<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Framework\Webapi\Request;
use Magento\Integration\Model\Validator\BearerTokenValidator;

/**
 * SOAP specific user context based on opaque tokens.
 */
class SoapUserContext implements UserContextInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Token
     */
    private $tokenFactory;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var string
     */
    private $userType;

    /**
     * @var bool
     */
    private $isRequestProcessed;

    /**
     * @var IntegrationServiceInterface
     */
    private IntegrationServiceInterface $integrationService;

    /**
     * @var BearerTokenValidator
     */
    private BearerTokenValidator $bearerTokenValidator;

    /**
     * Initialize dependencies.
     *
     * @param Request $request
     * @param TokenFactory $tokenFactory
     * @param IntegrationServiceInterface $integrationService
     * @param BearerTokenValidator|null $bearerTokenValidator
     */
    public function __construct(
        Request $request,
        TokenFactory $tokenFactory,
        IntegrationServiceInterface $integrationService,
        ?BearerTokenValidator $bearerTokenValidator = null
    ) {
        $this->request = $request;
        $this->tokenFactory = $tokenFactory;
        $this->integrationService = $integrationService;
        $this->bearerTokenValidator = $bearerTokenValidator ?? ObjectManager::getInstance()
            ->get(BearerTokenValidator::class);
    }

    /**
     * @inheritdoc
     */
    public function getUserId() //phpcs:ignore CopyPaste
    {
        $this->processRequest();
        return $this->userId;
    }

    /**
     * @inheritdoc
     */
    public function getUserType() //phpcs:ignore CopyPaste
    {
        $this->processRequest();
        return $this->userType;
    }

    /**
     * Finds the bearer token and looks up the value.
     *
     * @return void
     */
    private function processRequest() //phpcs:ignore CopyPaste
    {
        if ($this->isRequestProcessed) {
            return;
        }
        $authorizationHeaderValue = $this->request->getHeader('Authorization');
        if (!$authorizationHeaderValue) {
            $this->isRequestProcessed = true;
            return;
        }
        $headerPieces = explode(" ", $authorizationHeaderValue);
        if (count($headerPieces) !== 2) {
            $this->isRequestProcessed = true;
            return;
        }
        $tokenType = strtolower($headerPieces[0]);
        if ($tokenType !== 'bearer') {
            $this->isRequestProcessed = true;
            return;
        }

        $bearerToken = $headerPieces[1];

        /** @var Token $token */
        $token = $this->tokenFactory->create()->load($bearerToken, 'token');
        if (!$token->getId() || $token->getRevoked()) {
            $this->isRequestProcessed = true;
            return;
        }
        if (((int) $token->getUserType()) === UserContextInterface::USER_TYPE_INTEGRATION) {
            $integration = $this->integrationService->findByConsumerId($token->getConsumerId());
            if ($this->bearerTokenValidator->isIntegrationAllowedAsBearerToken($integration)) {
                $this->userId = $integration->getId();
                $this->userType = UserContextInterface::USER_TYPE_INTEGRATION;
            }
        }
        $this->isRequestProcessed = true;
    }
}
