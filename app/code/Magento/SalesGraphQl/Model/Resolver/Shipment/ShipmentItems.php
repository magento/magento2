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
use Magento\SalesGraphQl\Model\Shipment\ItemProvider;

/**
 * Resolve items included in shipment
 */
class ShipmentItems implements ResolverInterface
{
    /**
     * @var ItemProvider
     */
    private $shipmentItemProvider;

    /**
     * @param ItemProvider $shipmentItemProvider
     */
    public function __construct(ItemProvider $shipmentItemProvider)
    {
        $this->shipmentItemProvider = $shipmentItemProvider;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!($value['model'] ?? null) instanceof ShipmentInterface) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var ShipmentInterface $shipment */
        $shipment = $value['model'];

        return $this->shipmentItemProvider->getItemData($shipment);
    }
}
