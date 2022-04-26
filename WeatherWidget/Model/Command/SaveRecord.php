<?php
declare(strict_types=1);

namespace Tsg\WeatherWidget\Model\Command;

use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Tsg\WeatherWidget\Model\ResourceModel\Record as RecordResource;
use Tsg\WeatherWidgetApi\Api\Data\RecordInterface;

/**
 * Save weather record command.
 */
class SaveRecord
{
    private RecordResource $recordResource;

    /**
     * @param RecordResource $recordResource
     */
    public function __construct(RecordResource $recordResource)
    {
        $this->recordResource = $recordResource;
    }

    /**
     * Save weather record to db.
     *
     * @param RecordInterface $record
     * @throws CouldNotSaveException
     */
    public function execute(RecordInterface $record): void
    {
        try {
            $this->recordResource->save($record);
        } catch (Exception $e) {
            throw new CouldNotSaveException(__('Could not save the weather record: %1', $e->getMessage()));
        }
    }
}
