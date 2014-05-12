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

/**
 * Product mass attribute update websites tab
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab;

use Magento\Store\Model\Group;
use Magento\Store\Model\Website;

class Websites extends \Magento\Backend\Block\Widget implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @return Website[]
     */
    public function getWebsiteCollection()
    {
        return $this->_storeManager->getWebsites();
    }

    /**
     * @param Website $website
     * @return Group[]
     */
    public function getGroupCollection(Website $website)
    {
        return $website->getGroups();
    }

    /**
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
     * @return string
     */
    public function getTabLabel()
    {
        return __('Websites');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return __('Websites');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
