<?php
declare(strict_types=1);

namespace Tsg\WeatherWidget\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Tsg\WeatherWidgetApi\Api\Data\RecordInterface;

/**
 * Weather record resource model.
 */
class Record extends AbstractDb
{
    public const TABLE = 'tsg_weather_widget_record';

    /**
     * @inheritdoc
     */
    protected $_idFieldName = RecordInterface::RECORD_ID;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE, RecordInterface::RECORD_ID);
    }
}
