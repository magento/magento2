<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;

class OrderItem implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        if (!isset($value['model']) && !($value['model'] instanceof Order)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Order $parentOrder */
        $parentOrder = $value['model'];
        /** @var OrderItemInterface $item */
        $orderItems = [];
        foreach ($parentOrder->getItems() as $key => $item) {
            $options = $this->getItemOptions($item);
            $orderItems[$key] = [
                'parent_product_sku' => $item->getParentItem() ? $item->getParentItem()->getSku() : null,
                'product_name' => $item->getName(),
                'product_sale_price' => [
                    'currency' => $parentOrder->getOrderCurrencyCode(),
                    'value' => $item->getPrice(),
                ],
                'product_sku' => $item->getSku(),
                'product_url' => 'url',
                'quantity_ordered' => $item->getQtyOrdered(),
                'selected_options' => $options['selected_options'] ?? [],
                'entered_options' => $options['entered_options'] ?? [],
            ];
        }
        return $orderItems;
    }

    /**
     * Get Order item options.
     *
     * @param OrderItemInterface $orderItem
     * @return array
     */
    public function getItemOptions(OrderItemInterface $orderItem): array
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
    public function processOptions(array $options): array
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
     * @param array $attributesFnfo
     * @return array
     */
    public function processAttributesInfo(array $attributesFnfo): array
    {
        $selectedOptions = [];
        foreach ($attributesFnfo ?? [] as $option) {
            $selectedOptions[] = [
                'id' => $option['label'],
                'value' => $option['print_value'] ?? $option['value'],
            ];
        }
        return ['selected_options' => $selectedOptions, 'entered_options' => []];
    }
}
