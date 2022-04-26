<?php
declare(strict_types=1);

namespace Tsg\WeatherWidgetApi\Api;

use Tsg\WeatherWidgetApi\Api\Data\CoordinatesInterface;

/**
 * Service to get city coordinates
 */
interface GetCityCoordinatesInterface
{
    public const API_REQUEST_ENDPOINT = 'geo/1.0/direct';

    /**
     * Get coordinates by city name.
     *
     * @return CoordinatesInterface
     */
    public function execute(): CoordinatesInterface;
}
