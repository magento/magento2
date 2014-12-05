<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Cms\Model;

use Magento\Cms\Api\Data\BlockInterface;
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
class Block extends \Magento\Framework\Model\AbstractModel implements IdentityInterface, BlockInterface
{
    /**
     * CMS block cache tag
     */
    const CACHE_TAG = 'cms_block';

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
        return array(self::CACHE_TAG . '_' . $this->getId());
    }

    /**
     * Retrieve block id
     *
     * @return int
     */
    public function getId()
    {
        return $this->_getData(BlockInterface::ID);
    }

    /**
     * Retrieve block identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return (string) $this->_getData(BlockInterface::IDENTIFIER);
    }

    /**
     * Retrieve block title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_getData(BlockInterface::TITLE);
    }

    /**
     * Retrieve block content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->_getData(BlockInterface::CONTENT);
    }

    /**
     * Retrieve block creation time
     *
     * @return string
     */
    public function getCreationTime()
    {
        return $this->_getData(BlockInterface::CREATION_TIME);
    }

    /**
     * Retrieve block update time
     *
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->_getData(BlockInterface::UPDATE_TIME);
    }

    /**
     * Retrieve block status
     *
     * @return int
     */
    public function getIsActive()
    {
        return $this->_getData(BlockInterface::IS_ACTIVE);
    }
}
