<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Search\Api\Data\SynonymGroupInterface;

/**
 * Class \Magento\Search\Model\SynonymGroup
 *
 * @since 2.1.0
 */
class SynonymGroup extends AbstractModel implements SynonymGroupInterface
{
    /**
     * Init resource model
     *
     * @return void
     * @since 2.1.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Search\Model\ResourceModel\SynonymGroup::class);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getGroupId()
    {
        return $this->getData('group_id');
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function setGroupId($groupId)
    {
        $this->setData('group_id', $groupId);
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getWebsiteId()
    {
        return $this->getData('website_id') === null ? 0 : $this->getData('website_id');
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function setWebsiteId($websiteId)
    {
        $this->setData('website_id', $websiteId);
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getStoreId()
    {
        return $this->getData('store_id') === null ? 0 : $this->getData('store_id');
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function setStoreId($storeId)
    {
        $this->setData('store_id', $storeId);
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getSynonymGroup()
    {
        return $this->getData('synonyms');
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function setSynonymGroup($synonymGroup)
    {
        $this->setData('synonyms', $synonymGroup);
        return $this;
    }

    /**
     *  sets the 'scope_id' to website:storeviewid
     *
     * @return void
     * @since 2.1.0
     */
    public function setScope()
    {
        $this->setData('scope_id', $this->getWebsiteId() . ':' . $this->getStoreId());
    }
}
