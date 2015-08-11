<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Resource\Online\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

/**
 * Flat customer online grid collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends SearchResult
{
    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getMainTable()])
            ->joinLeft(
                ['customer' => $this->getTable('customer_entity')],
                'customer.entity_id = main_table.customer_id',
                ['email', 'firstname', 'lastname']
            );
        return $this;
    }
}
