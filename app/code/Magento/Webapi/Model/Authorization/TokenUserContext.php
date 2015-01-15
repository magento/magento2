<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Integration\Service\V1\Integration as IntegrationService;
use Magento\Webapi\Controller\Request;

/**
 * A user context determined by tokens in a HTTP request Authorization header.
 */
class TokenUserContext implements UserContextInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Token
     */
    protected $tokenFactory;

    /**
     * @var int
     */
    protected $userId;

    /**
     * @var string
     */
    protected $userType;

    /**
     * @var bool
     */
    protected $isRequestProcessed;

    /**
     * @var IntegrationService
     */
    protected $integrationService;

    /**
     * Initialize dependencies.
     *
     * @param Request $request
     * @param TokenFactory $tokenFactory
     * @param IntegrationService $integrationService
     */
    public function __construct(
        Request $request,
        TokenFactory $tokenFactory,
        IntegrationService $integrationService
    ) {
        $this->request = $request;
        $this->tokenFactory = $tokenFactory;
        $this->integrationService = $integrationService;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserId()
    {
        $this->processRequest();
        return $this->userId;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserType()
    {
        $this->processRequest();
        return $this->userType;
    }

    /**
     * Finds the bearer token and looks up the value.
     *
     * @return void
     */
    protected function processRequest()
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
        $token = $this->tokenFactory->create()->loadByToken($bearerToken);

        if (!$token->getId() || $token->getRevoked()) {
            $this->isRequestProcessed = true;
            return;
        }

        $this->setUserDataViaToken($token);
        $this->isRequestProcessed = true;
    }

    /**
     * @param Token $token
     * @return void
     */
    protected function setUserDataViaToken(Token $token)
    {
        $this->userType = $token->getUserType();
        switch ($this->userType) {
            case UserContextInterface::USER_TYPE_INTEGRATION:
                $this->userId = $this->integrationService->findByConsumerId($token->getConsumerId())->getId();
                $this->userType = UserContextInterface::USER_TYPE_INTEGRATION;
                break;
            case UserContextInterface::USER_TYPE_ADMIN:
                $this->userId = $token->getAdminId();
                $this->userType = UserContextInterface::USER_TYPE_ADMIN;
                break;
            case UserContextInterface::USER_TYPE_CUSTOMER:
                $this->userId = $token->getCustomerId();
                $this->userType = UserContextInterface::USER_TYPE_CUSTOMER;
                break;
            default:
                /* this is an unknown user type so reset the cached user type */
                $this->userType = null;
        }
    }
}
