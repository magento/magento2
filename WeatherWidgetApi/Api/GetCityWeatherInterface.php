<?php
declare(strict_types=1);

namespace Tsg\WeatherWidgetApi\Api;

/**
 * Fetch city weather data interface.
 */
interface GetCityWeatherInterface
{
    public const API_REQUEST_ENDPOINT = 'data/2.5/weather';

    /**
     * Fetch city weather data.
     *
     * @return void
     */
    public function execute(): void;
}
