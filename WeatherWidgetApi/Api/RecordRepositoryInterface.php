<?php
declare(strict_types=1);

namespace Tsg\WeatherWidgetApi\Api;

use Tsg\WeatherWidgetApi\Api\Data\RecordInterface;

/**
 * Weather record repository interface.
 */
interface RecordRepositoryInterface
{
    /**
     * Save weather record to db.
     *
     * @param \Tsg\WeatherWidgetApi\Api\Data\RecordInterface $record
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function save(RecordInterface $record): void;
}
