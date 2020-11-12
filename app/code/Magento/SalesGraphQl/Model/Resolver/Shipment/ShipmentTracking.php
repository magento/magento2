<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver\Shipment;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\ShipmentInterface;

/**
 * Resolve shipment tracking information
 */
class ShipmentTracking implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model']) && !($value['model'] instanceof ShipmentInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var ShipmentInterface $shipment */
        $shipment = $value['model'];
        $tracks = $shipment->getTracks();

        $shipmentTracking = [];
        foreach ($tracks as $tracking) {
            $shipmentTracking[] = [
                'title' => $tracking->getTitle(),
                'carrier' => $tracking->getCarrierCode(),
                'number' => $tracking->getTrackNumber(),
                'model' => $tracking
            ];
        }

        return $shipmentTracking;
    }
}
