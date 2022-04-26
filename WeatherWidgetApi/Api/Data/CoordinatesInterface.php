<?php
declare(strict_types=1);

namespace Tsg\WeatherWidgetApi\Api\Data;

/**
 * City coordinates data interface.
 */
interface CoordinatesInterface
{
    public const LONGITUDE = 'longitude';

    public const LATITUDE = 'latitude';

    /**
     * Return longitude of the city.
     *
     * @return float
     */
    public function getLongitude(): float;

    /**
     * Return latitude of the city.
     *
     * @return float
     */
    public function getLatitude(): float;
}
