<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Resource\Sales\Order;

/**
 * Sales order tax resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Tax extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        $connectionName = null
    ) {
        if (null === $connectionName) {
            $connectionName = 'sales';
        }
        parent::__construct($context, $connectionName);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_order_tax', 'tax_id');
    }
}
