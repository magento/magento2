<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver\OrderShipment;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\SalesGraphQl\Model\SalesItem\SalesItemFactory;
use function base64_encode;

/**
 * Resolve items included in shipment
 */
class ShipmentItems implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model']) && !($value['model'] instanceof ShipmentInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        if (!isset($value['order']) && !($value['order'] instanceof OrderInterface)) {
            throw new LocalizedException(__('"order" value should be specified'));
        }
        /** @var ShipmentInterface $shipment */
        $shipment = $value['model'];
        $order = $value['order'];

        $shipmentItems = [];
        foreach ($shipment->getItems() as $item) {
            $shipmentItems[] = [
                'id' => base64_encode($item->getEntityId()),
                'product_name' => $item->getName(),
                'product_sku' => $item->getSku(),
                'product_sale_price' => [
                    'value' => $item->getPrice(),
                    'currency' => $order->getOrderCurrencyCode()
                ],
                'quantity_shipped' => $item->getQty(),
                'model' => $item,
            ];
        }

        return $shipmentItems;
    }
}
