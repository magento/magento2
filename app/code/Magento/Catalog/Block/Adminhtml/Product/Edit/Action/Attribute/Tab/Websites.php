<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab;

use Magento\Store\Model\Group;
use Magento\Store\Model\Website;

/**
 * Product mass attribute update websites tab
 *
 * @api
 * @since 100.0.2
 */
class Websites extends \Magento\Backend\Block\Widget implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Return collection of websites
     *
     * @return Website[]
     */
    public function getWebsiteCollection()
    {
        return $this->_storeManager->getWebsites();
    }

    /**
     * Return collection of groups
     *
     * @param Website $website
     * @return Group[]
     */
    public function getGroupCollection(Website $website)
    {
        return $website->getGroups();
    }

    /**
     * Return collection of stores
     *
     * @param Group $group
     * @return array
     */
    public function getStoreCollection(Group $group)
    {
        return $group->getStores();
    }

    /**
     * Tab settings
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Websites');
    }

    /**
     * Return tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Websites');
    }

    /**
     * Return true always
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Return false always
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
