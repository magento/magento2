<?php
declare(strict_types=1);

namespace Tsg\WeatherWidgetConfig\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Tsg\WeatherWidgetApi\Api\Data\WeatherWidgetConfigInterface;

/**
 * Weather widget config data.
 */
class WeatherWidgetConfig implements WeatherWidgetConfigInterface
{
    private ScopeConfigInterface $config;

    /**
     * @param ScopeConfigInterface $config
     */
    public function __construct(ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return (bool) $this->config->getValue(
            self::WEATHER_WIDGET_CONFIG_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritDoc
     */
    public function getApiUrl(): string
    {
        return $this->config->getValue(
            self::WEATHER_WIDGET_CONFIG_PATH_API_URL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritDoc
     */
    public function getApiKey(): string
    {
        return $this->config->getValue(
            self::WEATHER_WIDGET_CONFIG_PATH_API_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritDoc
     */
    public function getCity(): string
    {
        return $this->config->getValue(
            self::WEATHER_WIDGET_CONFIG_PATH_CITY,
            ScopeInterface::SCOPE_STORE
        );
    }
}
