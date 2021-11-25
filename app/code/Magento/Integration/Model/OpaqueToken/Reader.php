<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Model\OpaqueToken;

use Magento\Integration\Api\Data\UserToken;
use Magento\Integration\Api\Exception\UserTokenException;
use Magento\Integration\Api\UserTokenReaderInterface;
use Magento\Integration\Model\CustomUserContext;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Integration\Helper\Oauth\Data as OauthHelper;

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
     * @param TokenFactory $tokenFactory
     * @param OauthHelper $helper
     */
    public function __construct(TokenFactory $tokenFactory, OauthHelper $helper)
    {
        $this->tokenFactory = $tokenFactory;
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function read(string $token): UserToken
    {
        /** @var Token $tokenModel */
        $tokenModel = $this->tokenFactory->create();
        $tokenModel = $tokenModel->load($token, 'token');

        if (!$tokenModel->getId()) {
            throw new UserTokenException('Token does not exist');
        }
        if ($tokenModel->getRevoked()) {
            throw new UserTokenException('Token was revoked');
        }
        $userType = (int) $tokenModel->getUserType();
        if ($userType !== CustomUserContext::USER_TYPE_ADMIN && $userType !== CustomUserContext::USER_TYPE_CUSTOMER) {
            throw new UserTokenException('Invalid token found');
        }
        if ($userType === CustomUserContext::USER_TYPE_ADMIN) {
            $userId = $tokenModel->getAdminId();
        } else {
            $userId = $tokenModel->getCustomerId();
        }
        if (!$userId) {
            throw new UserTokenException('Invalid token found');
        }
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
}
