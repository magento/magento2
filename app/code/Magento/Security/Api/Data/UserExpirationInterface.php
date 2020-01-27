<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Security\Api\Data;

/**
 * Interface UserExpirationInterface to be used as a DTO for expires_at property on User model.
 */
interface UserExpirationInterface
{

    const EXPIRES_AT = 'expires_at';

    const USER_ID = 'user_id';

    /**
     * `expires_at` getter.
     *
     * @return string
     */
    public function getExpiresAt();

    /**
     * `expires_at` setter.
     *
     * @param string $expiresAt
     * @return $this
     */
    public function setExpiresAt($expiresAt);

    /**
     * `user_id` getter.
     *
     * @return string
     */
    public function getUserId();

    /**
     * `user_id` setter.
     *
     * @param string $userId
     * @return $this
     */
    public function setUserId($userId);
}
