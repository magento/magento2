<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Shipment\Item;

use Magento\NegotiableQuote\Model\PriceCurrency;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;

/**
 * Format shipment item for GraphQl output
 */
class ShipmentItemFormatter implements FormatterInterface
{
    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * ShipmentItemFormatter constructor.
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(PriceCurrency $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @inheritDoc
     */
    public function formatShipmentItem(ShipmentInterface $shipment, ShipmentItemInterface $item): ?array
    {
        $order = $shipment->getOrder();
        return [
            'id' => base64_encode($item->getEntityId()),
            'product_name' => $item->getName(),
            'product_sku' => $item->getSku(),
            'product_sale_price' => [
                'value' => $item->getPrice(),
                'currency' => $order->getOrderCurrencyCode(),
                'formatted' => $this->priceCurrency->format($item->getPrice(),false,null,null,$order->getOrderCurrencyCode())
            ],
            'product_type' => $item->getOrderItem()->getProductType(),
            'quantity_shipped' => $item->getQty(),
            'model' => $item,
        ];
    }
}
