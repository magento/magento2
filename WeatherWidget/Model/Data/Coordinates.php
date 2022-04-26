<?php
declare(strict_types=1);

namespace Tsg\WeatherWidget\Model\Data;

use Tsg\WeatherWidgetApi\Api\Data\CoordinatesInterface;

/**
 * City coordinates data object.
 */
class Coordinates implements CoordinatesInterface
{
    private float $longitude;

    private float $latitude;

    /**
     * @param float $longitude
     * @param float $latitude
     */
    public function __construct(
        float $longitude,
        float $latitude
    ) {
        $this->longitude = $longitude;
        $this->latitude = $latitude;
    }

    /**
     * @inheritDoc
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * @inheritDoc
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }
}
