<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\ResourceModel\Link\Purchased;

/**
 * Downloadable links purchased resource collection
 *
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Init resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Downloadable\Model\Link\Purchased::class,
            \Magento\Downloadable\Model\ResourceModel\Link\Purchased::class
        );
    }

    /**
     * Add purchased items to collection
     *
     * @return $this
     * @since 2.0.0
     */
    public function addPurchasedItemsToResult()
    {
        $this->getSelect()->join(
            ['pi' => $this->getTable('downloadable_link_purchased_item')],
            'pi.purchased_id=main_table.purchased_id'
        );
        return $this;
    }
}
