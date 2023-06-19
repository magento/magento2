<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Model\OpaqueToken;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Integration\Api\Data\UserToken;
use Magento\Integration\Api\Exception\UserTokenException;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Api\UserTokenReaderInterface;
use Magento\Integration\Model\Config\AuthorizationConfig;
use Magento\Integration\Model\CustomUserContext;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Integration\Helper\Oauth\Data as OauthHelper;
use Magento\Integration\Model\Validator\BearerTokenValidator;

/**
 * Reads user token data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Reader implements UserTokenReaderInterface
{
    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var OauthHelper
     */
    private $helper;

    /**
     * @var IntegrationServiceInterface
     */
    private IntegrationServiceInterface $integrationService;

    /**
     * @var BearerTokenValidator
     */
    private BearerTokenValidator $bearerTokenValidator;

    /**
     * @param TokenFactory $tokenFactory
     * @param OauthHelper $helper
     * @param IntegrationServiceInterface|null $integrationService
     * @param BearerTokenValidator|null $bearerTokenValidator
     */
    public function __construct(
        TokenFactory $tokenFactory,
        OauthHelper $helper,
        ?IntegrationServiceInterface $integrationService = null,
        ?BearerTokenValidator $bearerTokenValidator = null
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->helper = $helper;
        $this->integrationService = $integrationService ?? ObjectManager::getInstance()
            ->get(IntegrationServiceInterface::class);
        $this->bearerTokenValidator = $bearerTokenValidator ?? ObjectManager::getInstance()
            ->get(BearerTokenValidator::class);
    }

    /**
     * @inheritDoc
     */
    public function read(string $token): UserToken
    {

        $tokenModel = $this->getTokenModel($token);
        $userType = (int) $tokenModel->getUserType();
        $this->validateUserType($userType);
        $userId = $this->getUserId($tokenModel);

        $issued = \DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $tokenModel->getCreatedAt(),
            new \DateTimeZone('UTC')
        );
        $lifetimeHours = $userType === CustomUserContext::USER_TYPE_ADMIN
            ? $this->helper->getAdminTokenLifetime() : $this->helper->getCustomerTokenLifetime();
        $expires = $issued->add(new \DateInterval("PT{$lifetimeHours}H"));

        return new UserToken(new CustomUserContext((int) $userId, (int) $userType), new Data($issued, $expires));
    }

    /**
     * Create the token model from the input
     *
     * @param string $token
     * @return Token
     */
    private function getTokenModel(string $token): Token
    {
        $tokenModel = $this->tokenFactory->create();
        $tokenModel = $tokenModel->load($token, 'token');

        if (!$tokenModel->getId()) {
            throw new UserTokenException('Token does not exist');
        }
        if ($tokenModel->getRevoked()) {
            throw new UserTokenException('Token was revoked');
        }

        return $tokenModel;
    }

    /**
     * Validate the given user type
     *
     * @param int $userType
     * @throws UserTokenException
     */
    private function validateUserType(int $userType): void
    {
        if ($userType !== CustomUserContext::USER_TYPE_ADMIN
            && $userType !== CustomUserContext::USER_TYPE_CUSTOMER
            && $userType !== CustomUserContext::USER_TYPE_INTEGRATION
        ) {
            throw new UserTokenException('Invalid token found');
        }
    }

    /**
     * Determine the user id for a given token
     *
     * @param Token $tokenModel
     * @return int
     */
    private function getUserId(Token $tokenModel): int
    {
        $userType = (int)$tokenModel->getUserType();
        $userId = null;

        if ($userType === CustomUserContext::USER_TYPE_ADMIN) {
            $userId = $tokenModel->getAdminId();
        } elseif ($userType === CustomUserContext::USER_TYPE_INTEGRATION) {
            $integration = $this->integrationService->findByConsumerId($tokenModel->getConsumerId());
            if ($this->bearerTokenValidator->isIntegrationAllowedAsBearerToken($integration)) {
                $userId = $integration->getId();
            }
        } else {
            $userId = $tokenModel->getCustomerId();
        }
        if (!$userId) {
            throw new UserTokenException('Invalid token found');
        }

        return (int)$userId;
    }
}
