<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Model\OAuth;

use Magento\AdobeImsApi\Api\Data\TokenResponseInterface;
use Magento\Framework\DataObject;

/**
 * Represent the token response service data class
 */
class TokenResponse extends DataObject implements TokenResponseInterface
{
    private const ACCESS_TOKEN = 'access_token';
    private const REFRESH_TOKEN = 'refresh_token';
    private const SUB = 'sub';
    private const NAME = 'name';
    private const TOKEN_TYPE = 'token_type';
    private const GIVEN_NAME = 'given_name';
    private const EXPIRES_IN = 'expires_in';
    private const FAMILY_NAME = 'family_name';
    private const EMAIL = 'email';
    private const ERROR = 'error';

    /**
     * Get access token
     *
     * @return string
     */
    public function getAccessToken(): string
    {
        return (string)$this->getData(self::ACCESS_TOKEN);
    }

    /**
     * Get refresh token
     *
     * @return string
     */
    public function getRefreshToken(): string
    {
        return (string)$this->getData(self::REFRESH_TOKEN);
    }

    /**
     * Get sub
     *
     * @return string
     */
    public function getSub(): string
    {
        return (string)$this->getData(self::SUB);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->getData(self::NAME);
    }

    /**
     * Get token type
     *
     * @return string
     */
    public function getTokenType(): string
    {
        return (string)$this->getData(self::TOKEN_TYPE);
    }

    /**
     * Get given name
     *
     * @return string
     */
    public function getGivenName(): string
    {
        return (string)$this->getData(self::GIVEN_NAME);
    }

    /**
     * Get expires in
     *
     * @return int
     */
    public function getExpiresIn(): int
    {
        return (int)$this->getData(self::EXPIRES_IN);
    }

    /**
     * Get family name
     *
     * @return string
     */
    public function getFamilyName(): string
    {
        return (string)$this->getData(self::FAMILY_NAME);
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail(): string
    {
        return (string)$this->getData(self::EMAIL);
    }

    /**
     * Get error code
     *
     * @return string
     */
    public function getError(): string
    {
        return (string)$this->getData(self::ERROR);
    }
}
