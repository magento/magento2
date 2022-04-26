<?php
declare(strict_types=1);

namespace Tsg\WeatherWidget\Model;

use Tsg\WeatherWidget\Model\Command\SaveRecord;
use Tsg\WeatherWidgetApi\Api\Data\RecordInterface;
use Tsg\WeatherWidgetApi\Api\RecordRepositoryInterface;

/**
 * Weather record repository.
 */
class RecordRepository implements RecordRepositoryInterface
{
    private SaveRecord $saveRecord;

    public function __construct(
        SaveRecord $saveRecord
    ) {
        $this->saveRecord = $saveRecord;
    }

    /**
     * @inheritDoc
     */
    public function save(RecordInterface $record): void
    {
        $this->saveRecord->execute($record);
    }
}
