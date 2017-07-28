<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Model\ResourceModel\Price\Customer;

/**
 * ProductAlert Price Customer collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Collection extends \Magento\Customer\Model\ResourceModel\Customer\Collection
{
    /**
     * Join productalert price data to customer collection
     *
     * @param int $productId
     * @param int $websiteId
     * @return $this
     * @since 2.0.0
     */
    public function join($productId, $websiteId)
    {
        $this->getSelect()->join(
            ['alert' => $this->getTable('product_alert_price')],
            'e.entity_id=alert.customer_id',
            ['alert_price_id', 'price', 'add_date', 'last_send_date', 'send_count', 'status']
        );

        $this->getSelect()->where('alert.product_id=?', $productId);
        if ($websiteId) {
            $this->getSelect()->where('alert.website_id=?', $websiteId);
        }
        $this->_setIdFieldName('alert_price_id');
        $this->addAttributeToSelect('*');

        return $this;
    }
}
