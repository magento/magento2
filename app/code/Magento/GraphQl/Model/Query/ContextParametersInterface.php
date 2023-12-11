<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Query;

/**
 * Provide possibility to add custom parameters to context object
 *
 * @api
 */
interface ContextParametersInterface
{
    /**
     * Set type of a user
     *
     * @param int $userType
     * @return void
     */
    public function setUserType(int $userType): void;

    /**
     * Get type of a user
     *
     * @return int|null
     */
    public function getUserType(): ?int;

    /**
     * Set id of a user
     *
     * @param int $userId
     * @return void
     */
    public function setUserId(int $userId): void;

    /**
     * Get id of a user
     *
     * @return int|null
     */
    public function getUserId(): ?int;

    /**
     * Add an extension attribute
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function addExtensionAttribute(string $key, $value): void;

    /**
     * Get extension attributes data
     *
     * @return array
     */
    public function getExtensionAttributesData(): array;
}
