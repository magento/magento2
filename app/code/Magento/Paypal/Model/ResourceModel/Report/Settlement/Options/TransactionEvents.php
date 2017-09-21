<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\ResourceModel\Report\Settlement\Options;

/**
 * Transaction Events Types Options
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class TransactionEvents implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Paypal\Model\Report\Settlement\Row
     */
    protected $_model;

    /**
     * @param \Magento\Paypal\Model\Report\Settlement\Row $model
     */
    public function __construct(\Magento\Paypal\Model\Report\Settlement\Row $model)
    {
        $this->_model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->_model->getTransactionEvents();
    }
}
