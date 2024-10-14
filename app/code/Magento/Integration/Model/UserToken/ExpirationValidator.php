<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Model\UserToken;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Integration\Api\Data\UserToken;
use Magento\Integration\Api\UserTokenValidatorInterface;
use Magento\Framework\Stdlib\DateTime\DateTime as DtUtil;

/**
 * Validates if a token is expired
 */
class ExpirationValidator implements UserTokenValidatorInterface
{
    /**
     * @var DtUtil
     */
    private $datetimeUtil;

    /**
     * @param DtUtil $datetimeUtil
     */
    public function __construct(DtUtil $datetimeUtil)
    {
        $this->datetimeUtil = $datetimeUtil;
    }

    /**
     * @inheritDoc
     */
    public function validate(UserToken $token): void
    {
        if (!$this->isIntegrationToken($token) && $this->isTokenExpired($token)) {
            throw new AuthorizationException(__('Consumer key has expired'));
        }
    }

    /**
     * Check if a token is expired
     *
     * @param UserToken $token
     * @return bool
     */
    private function isTokenExpired(UserToken $token): bool
    {
        return $token->getData()->getExpires()->getTimestamp() <= $this->datetimeUtil->gmtTimestamp();
    }

    /**
     * Check if a token is an integration token
     *
     * @param UserToken $token
     * @return bool
     */
    private function isIntegrationToken(UserToken $token): bool
    {
        return $token->getUserContext()->getUserType() === UserContextInterface::USER_TYPE_INTEGRATION;
    }
}
