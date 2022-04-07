<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\AdminAdobeIms\Model\ResourceModel\ImsToken as ImsTokenResource;
use Magento\AdminAdobeIms\Api\Data\ImsTokenInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\AdminAdobeIms\Api\Data\ImsTokenExtensionInterface;

/**
 * Represent the user profile service data class
 */
class ImsToken extends AbstractExtensibleModel implements ImsTokenInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    private const USER_ID = 'admin_user_id';
    private const ACCESS_TOKEN_HASH = 'access_token_hash';
    private const LAST_CHECK_TIME = 'last_check_time';
    private const CREATED_AT = 'created_at';
    private const UPDATED_AT = 'updated_at';
    private const ACCESS_TOKEN_EXPIRES_AT = 'access_token_expires_at';

    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(ImsTokenResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getUserId(): ?int
    {
        return (int) $this->getData(self::USER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setUserId(int $value): ImsTokenInterface
    {
        $this->setData(self::USER_ID, $value);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAccessTokenHash(): ?string
    {
        return $this->getData(self::ACCESS_TOKEN_HASH);
    }

    /**
     * @inheritdoc
     */
    public function setAccessTokenHash(string $value): ImsTokenInterface
    {
        $this->setData(self::ACCESS_TOKEN_HASH, $value);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(string $value): ImsTokenInterface
    {
        $this->setData(self::CREATED_AT, $value);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(string $value): ImsTokenInterface
    {
        $this->setData(self::UPDATED_AT, $value);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLastCheckTime(): ?string
    {
        return $this->getData(self::LAST_CHECK_TIME);
    }

    /**
     * @inheritdoc
     */
    public function setLastCheckTime(string $value): ImsTokenInterface
    {
        $this->setData(self::LAST_CHECK_TIME, $value);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAccessTokenExpiresAt(): ?string
    {
        return $this->getData(self::ACCESS_TOKEN_EXPIRES_AT);
    }

    /**
     * @inheritdoc
     */
    public function setAccessTokenExpiresAt(string $value): ImsTokenInterface
    {
        $this->setData(self::ACCESS_TOKEN_EXPIRES_AT, $value);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ImsTokenExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(ImsTokenExtensionInterface $extensionAttributes): ImsTokenInterface
    {
        $this->_setExtensionAttributes($extensionAttributes);

        return $this;
    }
}
