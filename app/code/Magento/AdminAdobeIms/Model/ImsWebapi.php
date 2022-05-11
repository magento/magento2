<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\AdminAdobeIms\Model\ResourceModel\ImsWebapi as ImsWebapiResource;
use Magento\AdminAdobeIms\Api\Data\ImsWebapiInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\AdminAdobeIms\Api\Data\ImsWebapiExtensionInterface;

/**
 * Represent the user profile service data class
 */
class ImsWebapi extends AbstractExtensibleModel implements ImsWebapiInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    private const ADMIN_USER_ID = 'admin_user_id';
    private const ACCESS_TOKEN_HASH = 'access_token_hash';
    private const ACCESS_TOKEN = 'access_token';
    private const LAST_CHECK_TIME = 'last_check_time';
    private const CREATED_AT = 'created_at';
    private const UPDATED_AT = 'updated_at';
    private const ACCESS_TOKEN_EXPIRES_AT = 'access_token_expires_at';

    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(ImsWebapiResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getAdminUserId(): ?int
    {
        return (int) $this->getData(self::ADMIN_USER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setAdminUserId(int $value): ImsWebapiInterface
    {
        $this->setData(self::ADMIN_USER_ID, $value);

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
    public function setAccessTokenHash(string $value): ImsWebapiInterface
    {
        $this->setData(self::ACCESS_TOKEN_HASH, $value);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAccessToken(): ?string
    {
        return $this->getData(self::ACCESS_TOKEN);
    }

    /**
     * @inheritdoc
     */
    public function setAccessToken(string $value): ImsWebapiInterface
    {
        $this->setData(self::ACCESS_TOKEN, $value);

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
    public function setCreatedAt(string $value): ImsWebapiInterface
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
    public function setUpdatedAt(string $value): ImsWebapiInterface
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
    public function setLastCheckTime(string $value): ImsWebapiInterface
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
    public function setAccessTokenExpiresAt(string $value): ImsWebapiInterface
    {
        $this->setData(self::ACCESS_TOKEN_EXPIRES_AT, $value);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ImsWebapiExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(ImsWebapiExtensionInterface $extensionAttributes): ImsWebapiInterface
    {
        $this->_setExtensionAttributes($extensionAttributes);

        return $this;
    }
}
