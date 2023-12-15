<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Integration\Api\Exception\UserTokenException;
use Magento\Integration\Api\UserTokenReaderInterface;
use Magento\Integration\Api\UserTokenValidatorInterface;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Framework\Webapi\Request;
use Magento\Framework\Stdlib\DateTime\DateTime as Date;
use Magento\Framework\Stdlib\DateTime;
use Magento\Integration\Helper\Oauth\Data as OauthHelper;

/**
 * A user context determined by tokens in a HTTP request Authorization header.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class TokenUserContext implements UserContextInterface, ResetAfterRequestInterface
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
     * @var UserTokenReaderInterface
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly UserTokenReaderInterface $userTokenReader;

    /**
     * @var UserTokenValidatorInterface
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly UserTokenValidatorInterface $userTokenValidator;

    /**
     * Initialize dependencies.
     *
     * @param Request $request
     * @param TokenFactory $tokenFactory
     * @param IntegrationServiceInterface $integrationService
     * @param DateTime|null $dateTime
     * @param Date|null $date
     * @param OauthHelper|null $oauthHelper
     * @param UserTokenReaderInterface|null $tokenReader
     * @param UserTokenValidatorInterface|null $tokenValidator
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        TokenFactory $tokenFactory,
        IntegrationServiceInterface $integrationService,
        DateTime $dateTime = null,
        Date $date = null,
        OauthHelper $oauthHelper = null,
        ?UserTokenReaderInterface $tokenReader = null,
        ?UserTokenValidatorInterface $tokenValidator = null
    ) {
        $this->request = $request;
        $this->tokenFactory = $tokenFactory;
        $this->integrationService = $integrationService;
        $this->userTokenReader = $tokenReader ?? ObjectManager::getInstance()->get(UserTokenReaderInterface::class);
        $this->userTokenValidator = $tokenValidator
            ?? ObjectManager::getInstance()->get(UserTokenValidatorInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getUserId()
    {
        $this->processRequest();
        return $this->userId;
    }

    /**
     * @inheritdoc
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
        try {
            $token = $this->userTokenReader->read($bearerToken);
        } catch (UserTokenException $exception) {
            $this->isRequestProcessed = true;
            return;
        }
        try {
            $this->userTokenValidator->validate($token);
        } catch (AuthorizationException $exception) {
            $this->isRequestProcessed = true;
            return;
        }

        $this->userType = $token->getUserContext()->getUserType();
        $this->userId = $token->getUserContext()->getUserId();
        $this->isRequestProcessed = true;
    }

    /**
     * Set user data based on user type received from token data.
     *
     * @param Token $token
     * @return void
     * @deprecated Since tokens are handled with UserTokenReader now.
     * @see TokenUserContext::processRequest()
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

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->isRequestProcessed = null;
        $this->userId = null;
        $this->userType = null;
    }
}
