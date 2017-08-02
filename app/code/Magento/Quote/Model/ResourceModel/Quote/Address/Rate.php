<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote\Address;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

/**
 * Quote address shipping rate resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Rate extends AbstractDb
{
    /**
     * Main table and field initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('quote_shipping_rate', 'rate_id');
    }
}
