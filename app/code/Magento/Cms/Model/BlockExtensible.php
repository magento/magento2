<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Model;

use Magento\Cms\Api\Data\BlockExtensibleExtensionInterface;
use Magento\Cms\Api\Data\BlockExtensibleInterface;
use Magento\Cms\Model\ResourceModel\Block as BlockResource;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class BlockExtensible
 *
 * @package Magento\Cms\Model
 *
 * TODO: Maybe this should be part of interface and we get rid of magic here
 *
 * TODO: Fix getters so they won't return any data set through $model->setData($key, $value)
 *
 * @method Block setStoreId(array $storeId)
 * @method array getStoreId()
 */
class BlockExtensible extends AbstractExtensibleModel implements BlockExtensibleInterface, IdentityInterface
{
    /**
     * @var string
     */
    public const CACHE_TAG = 'cms_b';

    /**
     * @var int
     */
    public const STATUS_ENABLED = 1;

    /**
     * @var int
     */
    public const STATUS_DISABLED = 0;

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = 'cms_block';

    /**
     * Init
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(BlockResource::class);
    }

    /**
     * Prevent blocks recursion
     *
     * @return BlockExtensible
     * @throws LocalizedException
     */
    public function beforeSave(): self
    {
        if ($this->hasDataChanges()) {
            $this->setUpdateTime(null);
        }

        $needle = 'block_id="' . $this->getId() . '"';

        if (strstr((string) $this->getContent(), $needle) == false) {
            return parent::beforeSave();
        }

        throw new LocalizedException(
            __('Make sure that static block content does not reference the block itself.')
        );
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getIdentifier()];
    }

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        $id = $this->getData(self::BLOCK_ID);
        return empty($id) ? null : (int) $id;
    }

    /**
     * Get identifier
     *
     * @return string|null
     */
    public function getIdentifier(): ?string
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Get content
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreationTime(): ?string
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime(): ?string
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->getData(self::IS_ACTIVE);
    }

    /**
     * Set id
     *
     * @param int|mixed $id
     * @return BlockExtensibleInterface
     */
    public function setId($id): BlockExtensibleInterface
    {
        $this->setData(self::BLOCK_ID, (int) $id);
        return $this;
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     *
     * @return BlockExtensibleInterface
     */
    public function setIdentifier($identifier): BlockExtensibleInterface
    {
        $this->setData(self::IDENTIFIER, $identifier);
        return $this;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return BlockExtensibleInterface
     */
    public function setTitle($title): BlockExtensibleInterface
    {
        $this->setData(self::TITLE, $title);
        return $this;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return BlockExtensibleInterface
     */
    public function setContent($content): BlockExtensibleInterface
    {
        $this->setData(self::CONTENT, $content);
        return $this;
    }

    /**
     * Set creation time
     *
     * @param string $creationTime
     *
     * @return BlockExtensibleInterface
     */
    public function setCreationTime($creationTime): BlockExtensibleInterface
    {
        $this->setData(self::CREATION_TIME, $creationTime);
        return $this;
    }

    /**
     * Set update time
     *
     * @param string $updateTime
     *
     * @return BlockExtensibleInterface
     */
    public function setUpdateTime($updateTime): BlockExtensibleInterface
    {
        $this->setData(self::UPDATE_TIME, $updateTime);
        return $this;
    }

    /**
     * Set is active
     *
     * @param bool|int $isActive
     *
     * @return BlockExtensibleInterface
     */
    public function setIsActive($isActive): BlockExtensibleInterface
    {
        $this->setData(self::IS_ACTIVE, $isActive);
        return $this;
    }

    /**
     * Get stores
     *
     * @return int[]
     */
    public function getStores(): array
    {
        $storeDataByStoresKey = $this->getData('stores');
        $storeDataByStoreIdKey = $this->getData('store_id');

        if (!empty($storeDataByStoresKey)) {
            return is_array($storeDataByStoresKey) ? $storeDataByStoresKey : [$storeDataByStoresKey];
        } elseif (!empty($storeDataByStoreIdKey)) {
            return is_array($storeDataByStoreIdKey) ? $storeDataByStoreIdKey : [$storeDataByStoreIdKey];
        }

        return [];
    }

    /**
     * TODO: Move to separate OptionSourceInterface instance
     *
     * Prepare block's statuses.
     *
     * @return array
     * @deprecated
     */
    public function getAvailableStatuses(): array
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Get extension attributes
     *
     * @return \Magento\Cms\Api\Data\BlockExtensibleExtensionInterface|null
     *
     * @codeCoverageIgnore
     */
    public function getExtensionAttributes(): ?BlockExtensibleExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set extension attributes
     *
     * @param BlockExtensibleExtensionInterface $extensionAttributes
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    public function setExtensionAttributes(BlockExtensibleExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
