<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver\OrderItem;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Process order item options to format for GraphQl output
 */
class OptionsProcessor
{
    /**
     * Serializer
     *
     * @var Json
     */
    private $serializer;

    /**
     * @param Json $serializer
     */
    public function __construct(Json $serializer)
    {
        $this->serializer = $serializer;
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

    /**
     * TODO: use this method for bundle options
     *
     * @param mixed $item
     * @return mixed|null
     */
    public function getSelectionAttributes($item)
    {
        if ($item instanceof \Magento\Sales\Model\Order\Item) {
            $options = $item->getProductOptions();
        } else {
            $options = $item->getOrderItem()->getProductOptions();
        }
        if (isset($options['bundle_selection_attributes'])) {
            return $this->serializer->unserialize($options['bundle_selection_attributes']);
        }
        return null;
    }
}
