<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model;

use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\LatLngInterface;

/**
 * @inheritdoc
 */
class LatLng implements LatLngInterface
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
     * @inheritdoc
     */
    public function getLat(): float
    {
        return $this->lat;
    }

    /**
     * @inheritdoc
     */
    public function getLng(): float
    {
        return $this->lng;
    }
}
