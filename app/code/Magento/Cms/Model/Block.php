<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Model\ResourceModel\Block as ResourceCmsBlock;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * CMS block model
 *
 * @method ResourceCmsBlock _getResource()
 * @method ResourceCmsBlock getResource()
 * @method Block setStoreId(array $storeId)
 * @method array getStoreId()
 * @since 2.0.0
 */
class Block extends AbstractModel implements BlockInterface, IdentityInterface
{
    /**
     * CMS block cache tag
     */
    const CACHE_TAG = 'cms_b';

    /**#@+
     * Block's statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**#@-*/

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Prefix of model events names
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'cms_block';

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Cms\Model\ResourceModel\Block::class);
    }

    /**
     * Prevent blocks recursion
     *
     * @return AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function beforeSave()
    {
        $needle = 'block_id="' . $this->getId() . '"';
        if (false == strstr($this->getContent(), $needle)) {
            return parent::beforeSave();
        }
        throw new \Magento\Framework\Exception\LocalizedException(
            __('Make sure that static block content does not reference the block itself.')
        );
    }

    /**
     * Get identities
     *
     * @return array
     * @since 2.0.0
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getIdentifier()];
    }

    /**
     * Retrieve block id
     *
     * @return int
     * @since 2.0.0
     */
    public function getId()
    {
        return $this->getData(self::BLOCK_ID);
    }

    /**
     * Retrieve block identifier
     *
     * @return string
     * @since 2.0.0
     */
    public function getIdentifier()
    {
        return (string)$this->getData(self::IDENTIFIER);
    }

    /**
     * Retrieve block title
     *
     * @return string
     * @since 2.0.0
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Retrieve block content
     *
     * @return string
     * @since 2.0.0
     */
    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * Retrieve block creation time
     *
     * @return string
     * @since 2.0.0
     */
    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Retrieve block update time
     *
     * @return string
     * @since 2.0.0
     */
    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Is active
     *
     * @return bool
     * @since 2.0.0
     */
    public function isActive()
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return BlockInterface
     * @since 2.0.0
     */
    public function setId($id)
    {
        return $this->setData(self::BLOCK_ID, $id);
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return BlockInterface
     * @since 2.0.0
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return BlockInterface
     * @since 2.0.0
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set content
     *
     * @param string $content
     * @return BlockInterface
     * @since 2.0.0
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return BlockInterface
     * @since 2.0.0
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return BlockInterface
     * @since 2.0.0
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Set is active
     *
     * @param bool|int $isActive
     * @return BlockInterface
     * @since 2.0.0
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Receive page store ids
     *
     * @return int[]
     * @since 2.0.0
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }

    /**
     * Prepare block's statuses.
     *
     * @return array
     * @since 2.1.0
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
}
