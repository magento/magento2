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

namespace Magento\CatalogInventory\Model\Resource\Stock\Status;

/**
 * Stock status collection resource model
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Magento\CatalogInventory\Model\Stock\Status',
            'Magento\CatalogInventory\Model\Resource\Stock\Status'
        );
    }

    /**
     * Filter status by website
     *
     * @param \Magento\Store\Model\Website $website
     * @return $this
     */
    public function addWebsiteFilter(\Magento\Store\Model\Website $website)
    {
        $this->addFieldToFilter('website_id', $website->getWebsiteId());
        return $this;
    }

    /**
     * Add filter by quantity to collection
     *
     * @param float $qty
     * @return $this
     */
    public function addQtyFilter($qty)
    {
        return $this->addFieldToFilter('main_table.qty', ['lteq' => $qty]);
    }

    /**
     * Initialize select object
     *
     * @return $this
     */
    protected function _initSelect()
    {
        return parent::_initSelect()->getSelect()->join(
            array('cp_table' => $this->getTable('catalog_product_entity')),
            'main_table.product_id = cp_table.entity_id',
            array('sku', 'type_id')
        );
    }
}
