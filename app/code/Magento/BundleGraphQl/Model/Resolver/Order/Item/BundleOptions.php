<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Resolver\Order\Item;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;

/**
 * Resolve bundle options items for order item
 */
class BundleOptions implements ResolverInterface
{
    /**
     * Option type name
     */
    private const OPTION_TYPE = 'bundle';

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /** @var Uid */
    private $uidEncoder;

    /**
     * @param ValueFactory $valueFactory
     * @param Json $serializer
     * @param Uid $uidEncoder
     */
    public function __construct(
        ValueFactory $valueFactory,
        Json $serializer,
        Uid $uidEncoder
    ) {
        $this->valueFactory = $valueFactory;
        $this->serializer = $serializer;
        $this->uidEncoder = $uidEncoder;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        return $this->valueFactory->create(function () use ($value) {
            if (!isset($value['model'])) {
                throw new LocalizedException(__('"model" value should be specified'));
            }
            if ($value['model'] instanceof OrderItemInterface) {
                $item = $value['model'];
                return $this->getBundleOptions($item, $value);
            }
            if ($value['model'] instanceof InvoiceItemInterface
                || $value['model'] instanceof ShipmentItemInterface
                || $value['model'] instanceof CreditmemoItemInterface) {
                $item = $value['model'];
                // Have to pass down order and item to map to avoid refetching all data
                return $this->getBundleOptions($item->getOrderItem(), $value);
            }
            return null;
        });
    }

    /**
     * Format bundle options and values from a parent bundle order item
     *
     * @param OrderItemInterface $item
     * @param array $formattedItem
     * @return array
     */
    private function getBundleOptions(
        OrderItemInterface $item,
        array $formattedItem
    ): array {
        $bundleOptions = [];
        if ($item->getProductType() === 'bundle') {
            $options = $item->getProductOptions();
            //loop through options
            foreach ($options['bundle_options'] ?? [] as $bundleOptionId => $bundleOption) {
                $bundleOptions[$bundleOptionId]['label'] = $bundleOption['label'] ?? '';
                $bundleOptions[$bundleOptionId]['id'] = isset($bundleOption['option_id']) ?
                    $this->uidEncoder->encode((string) $bundleOption['option_id']) : null;
                $bundleOptions[$bundleOptionId]['uid'] = isset($bundleOption['option_id']) ?
                    $this->uidEncoder->encode(self::OPTION_TYPE . '/' . $bundleOption['option_id']) : null;
                if (isset($bundleOption['option_id'])) {
                    $bundleOptions[$bundleOptionId]['values'] = $this->formatBundleOptionItems(
                        $item,
                        $formattedItem,
                        $bundleOption['option_id']
                    );
                } else {
                    $bundleOptions[$bundleOptionId]['values'] = [];
                }
            }
        }
        return $bundleOptions;
    }

    /**
     * Format Bundle items
     *
     * @param OrderItemInterface $item
     * @param array $formattedItem
     * @param string $bundleOptionId
     * @return array
     */
    private function formatBundleOptionItems(
        OrderItemInterface $item,
        array $formattedItem,
        string $bundleOptionId
    ) {
        $optionItems = [];
        // Find the item assign to the option
        /** @var OrderItemInterface $childrenOrderItem */
        foreach ($item->getChildrenItems() ?? [] as $childrenOrderItem) {
            $childOrderItemOptions = $childrenOrderItem->getProductOptions();
            $bundleChildAttributes = $this->serializer
                ->unserialize($childOrderItemOptions['bundle_selection_attributes'] ?? '');
            // Value Id is missing from parent, so we have to match the child to parent option
            if (isset($bundleChildAttributes['option_id'])
                && $bundleChildAttributes['option_id'] == $bundleOptionId) {

                $options = $childOrderItemOptions['info_buyRequest']
                ['bundle_option'][$bundleChildAttributes['option_id']];

                $optionDetails = [
                    self::OPTION_TYPE,
                    $bundleChildAttributes['option_id'],
                    is_array($options) ? implode(',', $options) : $options,
                    (int) $childOrderItemOptions['info_buyRequest']['qty']
                ];

                $optionItems[$childrenOrderItem->getItemId()] = [
                    'id' => $this->uidEncoder->encode((string) $childrenOrderItem->getItemId()),
                    'uid' => $this->uidEncoder->encode(implode('/', $optionDetails)),
                    'product_name' => $childrenOrderItem->getName(),
                    'product_sku' => $childrenOrderItem->getSku(),
                    'quantity' => $bundleChildAttributes['qty'],
                    'price' => [
                        //use options price, not child price
                        'value' => $bundleChildAttributes['price'],
                        //use currency from order
                        'currency' => $formattedItem['product_sale_price']['currency'] ?? null,
                    ]
                ];
            }
        }

        return $optionItems;
    }
}
