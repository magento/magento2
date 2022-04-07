<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Declare the ims token data service object
 * @api
 */
interface ImsTokenInterface extends ExtensibleDataInterface
{
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get user ID
     *
     * @return int|null
     */
    public function getUserId(): ?int;

    /**
     * Set user ID
     *
     * @param int $value
     * @return $this
     */
    public function setUserId(int $value): ImsTokenInterface;

    /**
     * Get access token hash
     *
     * @return string|null
     */
    public function getAccessTokenHash(): ?string;

    /**
     * Set access token hash
     *
     * @param string $value
     * @return $this
     */
    public function setAccessTokenHash(string $value): ImsTokenInterface;

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set creation time
     *
     * @param string $value
     * @return $this
     */
    public function setCreatedAt(string $value): ImsTokenInterface;

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * Set update time
     *
     * @param string $value
     * @return $this
     */
    public function setUpdatedAt(string $value): ImsTokenInterface;

    /**
     * Get last check time
     *
     * @return string|null
     */
    public function getLastCheckTime(): ?string;

    /**
     * Set last check time
     *
     * @param string $value
     * @return $this
     */
    public function setLastCheckTime(string $value): ImsTokenInterface;

    /**
     * Get expires time of token
     *
     * @return string|null
     */
    public function getAccessTokenExpiresAt(): ?string;

    /**
     * Set expires time of token
     *
     * @param string $value
     * @return $this
     */
    public function setAccessTokenExpiresAt(string $value): ImsTokenInterface;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return ImsTokenExtensionInterface
     */
    public function getExtensionAttributes(): ImsTokenExtensionInterface;

    /**
     * Set extension attributes
     *
     * @param ImsTokenExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        ImsTokenExtensionInterface $extensionAttributes
    ): ImsTokenInterface;
}
