<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Framework\Webapi\Request;

/**
 * A user context determined by tokens in a HTTP request Authorization header.
 * @since 2.0.0
 */
class TokenUserContext implements UserContextInterface
{
    /**
     * @var Request
     * @since 2.0.0
     */
    protected $request;

    /**
     * @var Token
     * @since 2.0.0
     */
    protected $tokenFactory;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $userId;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $userType;

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $isRequestProcessed;

    /**
     * @var IntegrationServiceInterface
     * @since 2.0.0
     */
    protected $integrationService;

    /**
     * Initialize dependencies.
     *
     * @param Request $request
     * @param TokenFactory $tokenFactory
     * @param IntegrationServiceInterface $integrationService
     * @since 2.0.0
     */
    public function __construct(
        Request $request,
        TokenFactory $tokenFactory,
        IntegrationServiceInterface $integrationService
    ) {
        $this->request = $request;
        $this->tokenFactory = $tokenFactory;
        $this->integrationService = $integrationService;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getUserId()
    {
        $this->processRequest();
        return $this->userId;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
