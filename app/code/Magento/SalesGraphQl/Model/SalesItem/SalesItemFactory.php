<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\SalesItem;

use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\SalesGraphQl\Model\Orders\GetDiscounts;
use Magento\SalesGraphQl\Model\SalesItem\Data\SalesItem;

/**
 * Create SalesItem object with data from OrderItem
 */
class SalesItemFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var GetDiscounts
     */
    private $getDiscounts;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param GetDiscounts $getDiscounts
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        GetDiscounts $getDiscounts
    ) {
        $this->objectManager = $objectManager;
        $this->getDiscounts = $getDiscounts;
    }

    /**
     * Create SalesItem object
     *
     * @param OrderItemInterface $orderItem
     * @param OrderInterface $order
     * @param array $additionalData
     * @return SalesItem
     */
    public function create(OrderItemInterface $orderItem, OrderInterface $order, array $additionalData = []): SalesItem
    {
        $options = $this->getItemOptions($orderItem);

        $salesItemData = [
            'product_name' => $orderItem->getName(),
            'product_sku' => $orderItem->getSku(),
            'product_sale_price' => [
                'currency' => $order->getOrderCurrencyCode(),
                'value' => $orderItem->getPrice(),
            ],
            'parent_product_name' => $orderItem->getParentItem() ? $orderItem->getParentItem()->getName() : null,
            'parent_product_sku' => $orderItem->getParentItem() ? $orderItem->getParentItem()->getSku() : null,
            'selected_options' => $options['selected_options'],
            'entered_options' => $options['entered_options'],
            'discounts' => $this->getDiscounts->execute($order),
        ];

        $salesItemData = array_merge_recursive($salesItemData, $additionalData);

        return $this->objectManager->create(SalesItem::class, ['data' => $salesItemData]);
    }

    /**
     * Get Order item options.
     *
     * @param OrderItemInterface $orderItem
     * @return array
     */
    private function getItemOptions(OrderItemInterface $orderItem): array
    {
        //build options array
        $optionsTypes = ['selected_options' => [], 'entered_options' => []];
        $options = $orderItem->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                $optionsTypes = $this->processOptions($options['options']);
            } elseif (isset($options['attributes_info'])) {
                $optionsTypes = $this->processAttributesInfo($options['attributes_info']);
            } elseif (isset($options['additional_options'])) {
                // TODO $options['additional_options']
            }
        }
        return $optionsTypes;
    }

    /**
     * Process options data
     *
     * @param array $options
     * @return array
     */
    private function processOptions(array $options): array
    {
        $selectedOptions = [];
        $enteredOptions = [];
        foreach ($options ?? [] as $option) {
            if (isset($option['option_type'])) {
                if (in_array($option['option_type'], ['field', 'area', 'file', 'date', 'date_time', 'time'])) {
                    $selectedOptions[] = [
                        'id' => $option['label'],
                        'value' => $option['print_value'] ?? $option['value'],
                    ];
                } elseif (in_array($option['option_type'], ['drop_down', 'radio', 'checkbox', 'multiple'])) {
                    $enteredOptions[] = [
                        'id' => $option['label'],
                        'value' => $option['print_value'] ?? $option['value'],
                    ];
                }
            }
        }
        return ['selected_options' => $selectedOptions, 'entered_options' => $enteredOptions];
    }

    /**
     * Process attributes info data
     *
     * @param array $attributesInfo
     * @return array
     */
    private function processAttributesInfo(array $attributesInfo): array
    {
        $selectedOptions = [];
        foreach ($attributesInfo ?? [] as $option) {
            $selectedOptions[] = [
                'id' => $option['label'],
                'value' => $option['print_value'] ?? $option['value'],
            ];
        }
        return ['selected_options' => $selectedOptions, 'entered_options' => []];
    }
}
