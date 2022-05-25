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
interface ImsWebapiInterface extends ExtensibleDataInterface
{
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get admin user ID
     *
     * @return int|null
     */
    public function getAdminUserId(): ?int;

    /**
     * Set admin user ID
     *
     * @param int $value
     * @return $this
     */
    public function setAdminUserId(int $value): ImsWebapiInterface;

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
    public function setAccessTokenHash(string $value): ImsWebapiInterface;

    /**
     * Get access token
     *
     * @return string|null
     */
    public function getAccessToken(): ?string;

    /**
     * Set access token
     *
     * @param string $value
     * @return $this
     */
    public function setAccessToken(string $value): ImsWebapiInterface;

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
    public function setCreatedAt(string $value): ImsWebapiInterface;

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
    public function setUpdatedAt(string $value): ImsWebapiInterface;

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
    public function setLastCheckTime(string $value): ImsWebapiInterface;

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
    public function setAccessTokenExpiresAt(string $value): ImsWebapiInterface;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\AdminAdobeIms\Api\Data\ImsWebapiExtensionInterface|null
     */
    public function getExtensionAttributes(): ImsWebapiExtensionInterface;

    /**
     * Set extension attributes
     *
     * @param \Magento\AdminAdobeIms\Api\Data\ImsWebapiExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(ImsWebapiExtensionInterface $extensionAttributes): ImsWebapiInterface;
}
