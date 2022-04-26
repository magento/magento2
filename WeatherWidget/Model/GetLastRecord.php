<?php
declare(strict_types=1);

namespace Tsg\WeatherWidget\Model;

use Tsg\WeatherWidget\Model\ResourceModel\Record\Collection;
use Tsg\WeatherWidget\Model\ResourceModel\Record\CollectionFactory;
use Tsg\WeatherWidgetApi\Api\Data\RecordInterface;
use Tsg\WeatherWidgetApi\Api\GetLastRecordInterface;

/**
 * Get last weather record.
 */
class GetLastRecord implements GetLastRecordInterface
{
    private CollectionFactory $recordCollectionFactory;

    /**
     * @param CollectionFactory $recordCollectionFactory
     */
    public function __construct(CollectionFactory $recordCollectionFactory)
    {
        $this->recordCollectionFactory = $recordCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(): ?RecordInterface
    {
        /** @var Collection $recordCollection */
        $recordCollection = $this->recordCollectionFactory->create();
        /** @var RecordInterface $record */
        $record = $recordCollection->addOrder(RecordInterface::RECORDED_AT)->getFirstItem();

        if (!$record->getRecordId()) {
            return null;
        }

        return $record;
    }
}
