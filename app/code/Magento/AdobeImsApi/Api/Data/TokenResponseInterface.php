<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeImsApi\Api\Data;

/**
 * Interface for the service data response object
 * @api
 */
interface TokenResponseInterface
{
    /**
     * Get access token
     *
     * @return string
     */
    public function getAccessToken(): string;

    /**
     * Get refresh token
     *
     * @return string
     */
    public function getRefreshToken(): string;

    /**
     * Get sub
     *
     * @return string
     */
    public function getSub(): string;

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get token type
     *
     * @return string
     */
    public function getTokenType(): string;

    /**
     * Get given name
     *
     * @return string
     */
    public function getGivenName(): string;

    /**
     * Get expires in
     *
     * @return int
     */
    public function getExpiresIn(): int;

    /**
     * Get family name
     *
     * @return string
     */
    public function getFamilyName(): string;

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail(): string;

    /**
     * Get error code
     *
     * @return string
     */
    public function getError(): string;
}
