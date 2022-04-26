<?php
declare(strict_types=1);

namespace Tsg\WeatherWidgetApi\Api\Data;

/**
 * Weather record data interface.
 */
interface RecordInterface
{
    public const RECORD_ID = 'record_id';

    public const CITY = 'city';

    public const TEMPERATURE = 'temperature';

    public const RECORDED_AT = 'recorded_at';

    /**
     * Return record id.
     *
     * @return int
     */
    public function getRecordId(): int;

    /**
     * Set record id.
     *
     * @param int $recordId
     * @return RecordInterface
     */
    public function setRecordId(int $recordId): RecordInterface;

    /**
     * Return city.
     *
     * @return string
     */
    public function getCity(): string;

    /**
     * Set city.
     *
     * @param string $city
     * @return RecordInterface
     */
    public function setCity(string $city): RecordInterface;

    /**
     * Return temperature.
     *
     * @return float
     */
    public function getTemperature(): float;

    /**
     * Set temperature.
     *
     * @param float $temperature
     * @return RecordInterface
     */
    public function setTemperature(float $temperature): RecordInterface;

    /**
     * Return recorded at time.
     *
     * @return string
     */
    public function getRecordedAt(): string;

    /**
     * Set recorded at time.
     *
     * @param string $recordedAt
     * @return RecordInterface
     */
    public function setRecordedAt(string $recordedAt): RecordInterface;
}
