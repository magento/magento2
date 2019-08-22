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
use Magento\Framework\Stdlib\DateTime\DateTime as Date;
use Magento\Framework\Stdlib\DateTime;
use Magento\Integration\Helper\Oauth\Data as OauthHelper;

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
     * @var IntegrationServiceInterface
     */
    protected $integrationService;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Date
     */
    private $date;

    /**
     * @var OauthHelper
     */
    private $oauthHelper;

    /**
     * Initialize dependencies.
     *
     * @param Request $request
     * @param TokenFactory $tokenFactory
     * @param IntegrationServiceInterface $integrationService
     * @param DateTime|null $dateTime
     * @param Date|null $date
     * @param OauthHelper|null $oauthHelper
     */
    public function __construct(
        Request $request,
        TokenFactory $tokenFactory,
        IntegrationServiceInterface $integrationService,
        DateTime $dateTime = null,
        Date $date = null,
        OauthHelper $oauthHelper = null
    ) {
        $this->request = $request;
        $this->tokenFactory = $tokenFactory;
        $this->integrationService = $integrationService;
        $this->dateTime = $dateTime ?: ObjectManager::getInstance()->get(
            DateTime::class
        );
        $this->date = $date ?: ObjectManager::getInstance()->get(
            Date::class
        );
        $this->oauthHelper = $oauthHelper ?: ObjectManager::getInstance()->get(
            OauthHelper::class
        );
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
     * Check if token is expired.
     *
     * @param Token $token
     * @return bool
     */
    private function isTokenExpired(Token $token): bool
    {
        if ($token->getUserType() == UserContextInterface::USER_TYPE_ADMIN) {
            $tokenTtl = $this->oauthHelper->getAdminTokenLifetime();
        } elseif ($token->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER) {
            $tokenTtl = $this->oauthHelper->getCustomerTokenLifetime();
        } else {
            // other user-type tokens are considered always valid
            return false;
        }

        if (empty($tokenTtl)) {
            return false;
        }

        if ($this->dateTime->strToTime($token->getCreatedAt()) < ($this->date->gmtTimestamp() - $tokenTtl * 3600)) {
            return true;
        }

        return false;
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

        if (!$token->getId() || $token->getRevoked() || $this->isTokenExpired($token)) {
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
