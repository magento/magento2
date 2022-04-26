<?php
declare(strict_types=1);

namespace Tsg\WeatherWidget\Model\ResourceModel\Record;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Tsg\WeatherWidget\Model\Record;
use Tsg\WeatherWidget\Model\ResourceModel\Record as RecordResourceModel;
use Tsg\WeatherWidgetApi\Api\Data\RecordInterface;

/**
 * Weather records collection.
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected $_idFieldName = RecordInterface::RECORD_ID;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Record::class, RecordResourceModel::class);
    }
}
