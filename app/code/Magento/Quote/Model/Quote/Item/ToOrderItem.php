<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Quote\Model\Quote\Item;

use Magento\Sales\Api\Data\OrderItemDataBuilder as OrderItemBuilder;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Class ToOrderItem
 */
class ToOrderItem
{
    protected $fields = [
        'sku', 'name', 'description', 'weight', 'is_qty_decimal', 'qty', 'is_virtual', 'original_price',
        'applied_rule_ids', 'additional_data', 'calculation_price', 'base_calculation_price', 'tax_percent',
        'tax_amount', 'tax_before_discount', 'base_tax_before_discount',  'tax_string',  'row_weight',  'row_total',
        'base_original_price', 'base_tax_amount', 'base_row_total', 'discount_percent', 'discount_amount',
        'base_discount_amount', 'base_cost', 'store_id', 'hidden_tax_amount', 'base_hidden_tax_amount', 'is_nominal'
    ];

    /**
     * @var OrderItemBuilder|\Magento\Framework\Api\Builder
     */
    protected $orderItemBuilder;

    /**
     * @param OrderItemBuilder $orderItemBuilder
     */
    public function __construct(
        OrderItemBuilder $orderItemBuilder
    ) {
        $this->orderItemBuilder = $orderItemBuilder;
    }

    /**
     * @param array $data
     * @return OrderItemInterface
     */
    public function convert(\Magento\Quote\Model\Quote\Item $object, $data = [])
    {
        return $this->orderItemBuilder
            ->populateWithArray(array_merge(array_intersect_key($object->getData(), array_flip($this->fields)), $data))
            ->setQtyOrdered($object->getQty())
            ->setPrice($object->getCalculationPrice())
            ->setBasePrice($object->getBaseCalculationPrice())
            ->create();
    }
}
