<?php

declare(strict_types=1);

namespace Chizhov\Status\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CustomerStatus extends AbstractDb
{
    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'customer_id';

    /**
     * @inheritDoc
     */
    protected $_mainTable = 'chizhov_customer_status';

    /**
     * @inheritDoc
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Resource initialization.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _construct()
    {
        $this->_init($this->getMainTable(), $this->getIdFieldName());
    }
}
