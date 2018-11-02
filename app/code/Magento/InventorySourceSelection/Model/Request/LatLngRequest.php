<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model\Request;

/**
 * Latitude and longitude DTO
 */
class LatLngRequest
{
    /**
     * @var float
     */
    private $lat;

    /**
     * @var float
     */
    private $lng;

    /**
     * LatLng constructor.
     *
     * @param float $lat
     * @param float $lng
     */
    public function __construct(float $lat, float $lng)
    {
        $this->lat = $lat;
        $this->lng = $lng;
    }

    /**
     * Get latitude
     *
     * @return float
     */
    public function getLat(): float
    {
        return $this->lat;
    }

    /**
     * Get longitude
     *
     * @return float
     */
    public function getLng(): float
    {
        return $this->lng;
    }

    /**
     * Get as string
     *
     * @return string
     */
    public function getAsString(): string
    {
        return $this->getLat() . ',' . $this->getLng();
    }
}
