<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\LineItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\SalesGraphQl\Model\Resolver\OrderItem\DataProvider as OrderItemProvider;

/**
 * Resolve bundle options items for order item
 */
class BundleOptions implements ResolverInterface
{
    /**
     * Serializer
     *
     * @var Json
     */
    private $serializer;

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
     * @param Json $serializer
     */
    public function __construct(
        ValueFactory $valueFactory,
        OrderItemProvider $orderItemProvider,
        Json $serializer
    ) {
        $this->valueFactory = $valueFactory;
        $this->orderItemProvider = $orderItemProvider;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        return $this->valueFactory->create(function () use ($value) {
            if (!isset($value['model'])) {
                throw new LocalizedException(__('"model" value should be specified'));
            }
            if ($value['model'] instanceof OrderItemInterface) {
                /** @var ExtensibleDataInterface $item */
                $item = $value['model'];
                return $this->getBundleOptions($item, null, null);
            }
            if ($value['model'] instanceof LineItemInterface) {
                /** @var LineItemInterface $item */
                $item = $value['model'];
                $lineItemToOrderItemMap = $value['line_item_to_order_item_map'];
                $order = $value['order'];
                // Have to pass down order and item to map to avoid refetching all data
                return $this->getBundleOptions($item->getOrderItem(), $order, $lineItemToOrderItemMap);
            }
            return null;
        });
    }

    /**
     * Format bundle options and values from a parent bundle order item
     *
     * @param OrderItemInterface $item
     * @param Order|null $order
     * @param array|null $lineItemToOrderItemMap
     * @return array
     */
    private function getBundleOptions(
        OrderItemInterface $item,
        Order $order = null,
        array $lineItemToOrderItemMap = null
    ): array {
        $bundleOptions = [];
        if ($item->getProductType() === 'bundle') {
            $options = $item->getProductOptions();
            //loop through options
            foreach ($options['bundle_options'] ?? [] as $bundleOptionId => $bundleOption) {
                $bundleOptions[$bundleOptionId]['label'] = $bundleOption['label'] ?? '';
                $bundleOptions[$bundleOptionId]['id'] = isset($bundleOption['option_id']) ?
                    base64_encode($bundleOption['option_id']) : null;
                $optionItems = $this->formatBundleOptionItems(
                    $item,
                    $bundleOption,
                    $lineItemToOrderItemMap
                );
                $bundleOptions[$bundleOptionId]['item_ids'] = $optionItems['item_ids'];
                $bundleOptions[$bundleOptionId]['items'] = $optionItems['items'] ?? [];
                $bundleOptions[$bundleOptionId]['order'] = $order;
            }
        }
        return $bundleOptions;
    }

    /**
     * Format Bundle items
     *
     * @param OrderItemInterface $item
     * @param array $bundleOption
     * @param array|null $lineItemToOrderItemMap
     * @return array
     */
    private function formatBundleOptionItems(
        OrderItemInterface $item,
        array $bundleOption,
        array $lineItemToOrderItemMap = null
    ) {
        $optionItems = [];
        $optionItems['item_ids'] = [];
        $optionItems['items'] = [];
        foreach ($bundleOption['value'] ?? [] as $bundleOptionValueKey => $bundleOptionValue) {
            // Find the item assign to the option
            /** @var OrderItemInterface $childrenOrderItem */
            foreach ($item->getChildrenItems() ?? [] as $childrenOrderItem) {
                $childOrderItemOptions = $childrenOrderItem->getProductOptions();
                $bundleChildAttributes = $this->serializer
                    ->unserialize($childOrderItemOptions['bundle_selection_attributes']);
                // Value Id is missing from parent, so we have to match the child to parent option
                if (isset($bundleChildAttributes['option_id'])
                    && $bundleChildAttributes['option_id'] == $bundleOption['option_id']) {
                    $optionItems['item_ids'][] = $childrenOrderItem->getItemId();
                    if ($lineItemToOrderItemMap) {
                        $optionItems['items'][] = $lineItemToOrderItemMap[$childrenOrderItem->getItemId()];
                    }
                }
            }
        }
        return $optionItems;
    }
}
