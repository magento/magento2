<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver\CreditMemo;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\SalesGraphQl\Model\OrderItem\DataProvider as OrderItemProvider;

/**
 * Resolve credit memos items data
 */
class CreditMemoItems implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var OrderItemProvider
     */
    private $orderItemProvider;

    /**
     * @param ValueFactory $valueFactory
     * @param OrderItemProvider $orderItemProvider
     */
    public function __construct(
        ValueFactory $valueFactory,
        OrderItemProvider $orderItemProvider
    ) {
        $this->valueFactory = $valueFactory;
        $this->orderItemProvider = $orderItemProvider;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!(($value['model'] ?? null) instanceof CreditmemoInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        if (!(($value['order'] ?? null) instanceof OrderInterface)) {
            throw new LocalizedException(__('"order" value should be specified'));
        }

        /** @var CreditmemoInterface $creditMemoModel */
        $creditMemoModel = $value['model'];
        /** @var OrderInterface $parentOrderModel */
        $parentOrderModel = $value['order'];

        return $this->valueFactory->create(
            $this->getCreditMemoItems($parentOrderModel, $creditMemoModel->getItems())
        );
    }

    /**
     * Get credit memo items data as a promise
     *
     * @param OrderInterface $order
     * @param array $creditMemoItems
     * @return \Closure
     */
    private function getCreditMemoItems(OrderInterface $order, array $creditMemoItems): \Closure
    {
        $orderItems = [];
        foreach ($creditMemoItems as $item) {
            $this->orderItemProvider->addOrderItemId((int)$item->getOrderItemId());
        }

        return function () use ($order, $creditMemoItems, $orderItems): array {
            foreach ($creditMemoItems as $creditMemoItem) {
                $orderItem = $this->orderItemProvider->getOrderItemById((int)$creditMemoItem->getOrderItemId());
                /** @var OrderItemInterface $orderItemModel */
                $orderItemModel = $orderItem['model'];
                if (!$orderItemModel->getParentItem()) {
                    $creditMemoItemData = $this->getCreditMemoItemData($order, $creditMemoItem);
                    if (!empty($creditMemoItemData)) {
                        $orderItems[$creditMemoItem->getOrderItemId()] = $creditMemoItemData;
                    }
                }
            }
            return $orderItems;
        };
    }

    /**
     * Get credit memo item data
     *
     * @param OrderInterface $order
     * @param CreditmemoItemInterface $creditMemoItem
     * @return array
     */
    private function getCreditMemoItemData(OrderInterface $order, CreditmemoItemInterface $creditMemoItem): array
    {
        $orderItem = $this->orderItemProvider->getOrderItemById((int)$creditMemoItem->getOrderItemId());
        return [
            'id' => base64_encode($creditMemoItem->getEntityId()),
            'product_name' => $creditMemoItem->getName(),
            'product_sku' => $creditMemoItem->getSku(),
            'product_sale_price' => [
                'value' => $creditMemoItem->getPrice(),
                'currency' => $order->getOrderCurrencyCode()
            ],
            'quantity_refunded' => $creditMemoItem->getQty(),
            'model' => $creditMemoItem,
            'product_type' => $orderItem['product_type'],
            'discounts' => $this->formatDiscountDetails($order, $creditMemoItem)
        ];
    }

    /**
     * Returns formatted information about an applied discount
     *
     * @param OrderInterface $associatedOrder
     * @param CreditmemoItemInterface $creditmemoItem
     * @return array
     */
    private function formatDiscountDetails(
        OrderInterface $associatedOrder,
        CreditmemoItemInterface $creditmemoItem
    ): array {
        if ($associatedOrder->getDiscountDescription() === null
            && $creditmemoItem->getDiscountAmount() == 0
            && $associatedOrder->getDiscountAmount() == 0
        ) {
            $discounts = [];
        } else {
            $discounts[] = [
                'label' => $associatedOrder->getDiscountDescription() ?? _('Discount'),
                'amount' => [
                    'value' => abs($creditmemoItem->getDiscountAmount()) ?? 0,
                    'currency' => $associatedOrder->getOrderCurrencyCode()
                ]
            ];
        }
        return $discounts;
    }
}
