<?php
declare(strict_types=1);

namespace Tsg\WeatherWidget\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Tsg\WeatherWidget\Model\ResourceModel\Record as RecordResourceModel;
use Tsg\WeatherWidgetApi\Api\Data\RecordInterface;

/**
 * Weather record data model.
 */
class Record extends AbstractExtensibleModel implements RecordInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(RecordResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getRecordId(): int
    {
        return (int)$this->getData(self::RECORD_ID);
    }

    /**
     * @inheritDoc
     */
    public function setRecordId(int $recordId): RecordInterface
    {
        $this->setData(self::RECORD_ID, $recordId);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCity(): string
    {
        return $this->getData(self::CITY);
    }

    /**
     * @inheritDoc
     */
    public function setCity(string $city): RecordInterface
    {
        $this->setData(self::CITY, $city);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTemperature(): float
    {
        return (float) $this->getData(self::TEMPERATURE);
    }

    /**
     * @inheritDoc
     */
    public function setTemperature(float $temperature): RecordInterface
    {
        $this->setData(self::TEMPERATURE, $temperature);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRecordedAt(): string
    {
        return $this->getData(self::RECORDED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setRecordedAt(string $recordedAt): RecordInterface
    {
        $this->setData(self::RECORDED_AT, $recordedAt);

        return $this;
    }
}
