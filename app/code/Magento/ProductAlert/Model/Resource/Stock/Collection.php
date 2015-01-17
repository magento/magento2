<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Model\Resource\Stock;

/**
 * Product alert for back in stock collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Define stock collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\ProductAlert\Model\Stock', 'Magento\ProductAlert\Model\Resource\Stock');
    }

    /**
     * Add website filter
     *
     * @param mixed $website
     * @return $this
     */
    public function addWebsiteFilter($website)
    {
        $adapter = $this->getConnection();
        if (is_null($website) || $website == 0) {
            return $this;
        }
        if (is_array($website)) {
            $condition = $adapter->quoteInto('website_id IN(?)', $website);
        } elseif ($website instanceof \Magento\Store\Model\Website) {
            $condition = $adapter->quoteInto('website_id=?', $website->getId());
        } else {
            $condition = $adapter->quoteInto('website_id=?', $website);
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
