<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Security\Api\Data;

use \Magento\Security\Api\Data\UserExpirationExtensionInterface;

/**
 * Interface UserExpirationInterface to be used as a DTO for expires_at property on User model.
 */
interface UserExpirationInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    public const EXPIRES_AT = 'expires_at';

    public const USER_ID = 'user_id';

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

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Security\Api\Data\UserExpirationExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Security\Api\Data\UserExpirationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(UserExpirationExtensionInterface $extensionAttributes);
}
