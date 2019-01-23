<?php

declare(strict_types=1);

namespace Chizhov\Status\Model\ResourceModel\CustomerStatus;

use Chizhov\Status\Model;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
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
    protected $_eventPrefix = 'chizhov_customer_status';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(Model\CustomerStatus::class, Model\ResourceModel\CustomerStatus::class);
    }
}
