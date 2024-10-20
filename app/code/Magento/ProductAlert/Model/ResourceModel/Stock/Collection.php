<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Model\ResourceModel\Stock;

/**
 * Product alert for back in stock collection
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define stock collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\ProductAlert\Model\Stock::class, \Magento\ProductAlert\Model\ResourceModel\Stock::class);
    }

    /**
     * Add website filter
     *
     * @param mixed $website
     * @return $this
     */
    public function addWebsiteFilter($website)
    {
        $connection = $this->getConnection();
        if ($website === null || $website == 0) {
            return $this;
        }
        if (is_array($website)) {
            $condition = $connection->quoteInto('website_id IN(?)', $website);
        } elseif ($website instanceof \Magento\Store\Model\Website) {
            $condition = $connection->quoteInto('website_id=?', $website->getId());
        } else {
            $condition = $connection->quoteInto('website_id=?', $website);
        }
        $this->addFilter('website_id', $condition, 'string');
        return $this;
    }

    /**
     * Add status filter
     *
     * @param int $status
     * @return $this
     */
    public function addStatusFilter($status)
    {
        $condition = $this->getConnection()->quoteInto('status=?', $status);
        $this->addFilter('status', $condition, 'string');
        return $this;
    }

    /**
     * Set order by customer
     *
     * @param string $sort
     * @return $this
     */
    public function setCustomerOrder($sort = 'ASC')
    {
        $this->getSelect()->order('customer_id ' . $sort);
        return $this;
    }
}
