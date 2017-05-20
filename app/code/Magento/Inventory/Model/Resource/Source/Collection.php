<?php

use Magento\Inventory\Model\Resource\Source as ResourceSource;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Inventory\Model\Source as SourceModel;

class Collection extends AbstractCollection
{
    /**
     * Initialize resource model
     * @return void
     *
     * @codingStandardsIgnore
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init(SourceModel::class, ResourceSource::class);
    }

    /**
     * Id field name getter
     *
     * @return string
     */
    public function getIdFieldName()
    {
        return SourceInterface::SOURCE_ID;
    }
}