<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

use Magento\Framework\Object\IdentityInterface;

/**
 * CMS block model
 *
 * @method \Magento\Cms\Model\Resource\Block _getResource()
 * @method \Magento\Cms\Model\Resource\Block getResource()
 * @method \Magento\Cms\Model\Block setTitle(string $value)
 * @method \Magento\Cms\Model\Block setIdentifier(string $value)
 * @method \Magento\Cms\Model\Block setContent(string $value)
 * @method \Magento\Cms\Model\Block setCreationTime(string $value)
 * @method \Magento\Cms\Model\Block setUpdateTime(string $value)
 * @method \Magento\Cms\Model\Block setIsActive(int $value)
 */
class Block extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    /**
     * CMS block cache tag
     */
    const CACHE_TAG = 'cms_block';

    const ID = 'block_id';
    const IDENTIFIER = 'identifier';
    const TITLE = 'title';
    const CONTENT = 'content';
    const CREATION_TIME = 'creation_time';
    const UPDATE_TIME ='update_time';
    const IS_ACTIVE ='is_active';

    /**
     * @var string
     */
    protected $_cacheTag = 'cms_block';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'cms_block';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Cms\Model\Resource\Block');
    }

    /**
     * Prevent blocks recursion
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @throws \Magento\Framework\Model\Exception
     */
    public function beforeSave()
    {
        $needle = 'block_id="' . $this->getId() . '"';
        if (false == strstr($this->getContent(), $needle)) {
            return parent::beforeSave();
        }
        throw new \Magento\Framework\Model\Exception(
            __('Make sure that static block content does not reference the block itself.')
        );
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Retrieve block id
     *
     * @return int
     */
    public function getId()
    {
        return $this->_getData(self::ID);
    }

    /**
     * Retrieve block identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return (string) $this->_getData(self::IDENTIFIER);
    }

    /**
     * Retrieve block title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_getData(self::TITLE);
    }

    /**
     * Retrieve block content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->_getData(self::CONTENT);
    }

    /**
     * Retrieve block creation time
     *
     * @return string
     */
    public function getCreationTime()
    {
        return $this->_getData(self::CREATION_TIME);
    }

    /**
     * Retrieve block update time
     *
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->_getData(self::UPDATE_TIME);
    }

    /**
     * Retrieve block status
     *
     * @return int
     */
    public function getIsActive()
    {
        return $this->_getData(self::IS_ACTIVE);
    }
}
