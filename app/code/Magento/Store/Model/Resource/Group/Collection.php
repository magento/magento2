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
namespace Magento\Store\Model\Resource\Group;

/**
 * Store group collection
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setFlag('load_default_store_group', false);
        $this->_init('Magento\Store\Model\Group', 'Magento\Store\Model\Resource\Group');
    }

    /**
     * Set flag for load default (admin) store
     *
     * @param boolean $loadDefault
     * @return $this
     */
    public function setLoadDefault($loadDefault)
    {
        return $this->setFlag('load_default_store_group', (bool)$loadDefault);
    }

    /**
     * Is load default (admin) store
     *
     * @return boolean
     */
    public function getLoadDefault()
    {
        return $this->getFlag('load_default_store_group');
    }

    /**
     * Add disable default store group filter to collection
     *
     * @return $this
     */
    public function setWithoutDefaultFilter()
    {
        return $this->addFieldToFilter('main_table.group_id', array('gt' => 0));
    }

    /**
     * Filter to discard stores without views
     *
     * @return $this
     */
    public function setWithoutStoreViewFilter()
    {
        return $this->addFieldToFilter('main_table.default_store_id', array('gt' => 0));
    }

    /**
     * Load collection data
     *
     * @return $this
     */
    public function _beforeLoad()
    {
        if (!$this->getLoadDefault()) {
            $this->setWithoutDefaultFilter();
        }
        $this->addOrder('main_table.name', self::SORT_ORDER_ASC);
        return parent::_beforeLoad();
    }

    /**
     * Convert collection items to array for select options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('group_id', 'name');
    }

    /**
     * Add filter by website to collection
     *
     * @param int|array $website
     * @return $this
     */
    public function addWebsiteFilter($website)
    {
        return $this->addFieldToFilter('main_table.website_id', array('in' => $website));
    }
}
