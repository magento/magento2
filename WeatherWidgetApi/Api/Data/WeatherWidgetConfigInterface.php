<?php
declare(strict_types=1);

namespace Tsg\WeatherWidgetApi\Api\Data;

/**
 * Weather widget config interface.
 */
interface WeatherWidgetConfigInterface
{
    /**
     * Weather Widget API Url.
     */
    public const WEATHER_WIDGET_CONFIG_PATH_ENABLED = 'weather_widget/settings/enabled';

    /**
     * Weather Widget API Url.
     */
    public const WEATHER_WIDGET_CONFIG_PATH_API_URL = 'weather_widget/settings/api_url';

    /**
     * Weather Widget API Key.
     */
    public const WEATHER_WIDGET_CONFIG_PATH_API_KEY = 'weather_widget/settings/api_key';

    /**
     * Weather Widget City.
     */
    public const WEATHER_WIDGET_CONFIG_PATH_CITY = 'weather_widget/settings/city';

    /**
     * Check is weather widget enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Get api url for weather widget.
     *
     * @return string
     */
    public function getApiUrl(): string;

    /**
     * Get api key for weather widget.
     *
     * @return string
     */
    public function getApiKey(): string;

    /**
     * Get city for weather widget.
     *
     * @return string
     */
    public function getCity(): string;
}
