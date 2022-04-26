<?php
declare(strict_types=1);

namespace Tsg\WeatherWidgetApi\Api;

use Tsg\WeatherWidgetApi\Api\Data\RecordInterface;

/**
 * Get last weather record interface.
 */
interface GetLastRecordInterface
{
    /**
     * Get last weather record.
     *
     * @return RecordInterface|null
     */
    public function execute(): ?RecordInterface;
}
