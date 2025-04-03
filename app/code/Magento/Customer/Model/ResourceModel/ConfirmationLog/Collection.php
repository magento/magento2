<?php

declare(strict_types=1);

namespace Magento\Customer\Model\ResourceModel\ConfirmationLog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Customer\Model\ConfirmationLog as Model;
use Magento\Customer\Model\ResourceModel\ConfirmationLog as ResourceModel;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct(): void
    {
        $this->_init(Model::class, ResourceModel::class);
    }

}
