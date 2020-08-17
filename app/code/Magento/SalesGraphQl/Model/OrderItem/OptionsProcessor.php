<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\OrderItem;

use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Process order item options to format for GraphQl output
 */
class OptionsProcessor
{
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
                        'label' => $option['label'],
                        'value' => $option['print_value'] ?? $option['value'],
                    ];
                } elseif (in_array($option['option_type'], ['drop_down', 'radio', 'checkbox', 'multiple'])) {
                    $enteredOptions[] = [
                        'label' => $option['label'],
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
                'label' => $option['label'],
                'value' => $option['print_value'] ?? $option['value'],
            ];
        }
        return ['selected_options' => $selectedOptions, 'entered_options' => []];
    }
}
