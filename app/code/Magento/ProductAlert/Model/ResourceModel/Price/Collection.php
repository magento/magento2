<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Model\ResourceModel\Price;

/**
 * Product alert for changed price collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define price collection
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\ProductAlert\Model\Price::class, \Magento\ProductAlert\Model\ResourceModel\Price::class);
    }

    /**
     * Add website filter
     *
     * @param mixed $website
     * @return $this
     * @since 2.0.0
     */
    public function addWebsiteFilter($website)
    {
        if ($website === null || $website == 0) {
            return $this;
        }
        if (is_array($website)) {
            $condition = $this->getConnection()->quoteInto('website_id IN(?)', $website);
        } elseif ($website instanceof \Magento\Store\Model\Website) {
            $condition = $this->getConnection()->quoteInto('website_id=?', $website->getId());
        } else {
            $condition = $this->getConnection()->quoteInto('website_id=?', $website);
        }
        $this->addFilter('website_id', $condition, 'string');
        return $this;
    }

    /**
     * Set order by customer
     *
     * @param string $sort
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerOrder($sort = 'ASC')
    {
        $this->getSelect()->order('customer_id ' . $sort);
        return $this;
    }
}
