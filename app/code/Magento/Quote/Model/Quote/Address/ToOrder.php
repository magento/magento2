<?php
/**
 * Created by PhpStorm.
 * User: akaplya
 * Date: 23.12.14
 * Time: 19:30
 */

namespace Magento\Quote\Model\Quote\Address;

use Magento\Sales\Api\Data\OrderDataBuilder as OrderBuilder;
use Magento\Sales\Api\Data\OrderInterface;
/**
 * Class ToOrder converter
 */
class ToOrder
{
    protected $fields = [
        'weight', 'shipping_method', 'shipping_description', 'shipping_rate', 'subtotal', 'tax_amount', 'tax_string',
        'discount_amount', 'shipping_amount', 'shipping_incl_tax', 'shipping_tax_amount', 'custbalance_amount',
        'grand_total', 'base_subtotal', 'base_tax_amount', 'base_discount_amount', 'base_shipping_amount',
        'base_shipping_incl_tax', 'base_shipping_tax_amount', 'base_custbalance_amount', 'base_grand_total',
        'hidden_tax_amount', 'base_hidden_tax_amount', 'shipping_hidden_tax_amount', 'base_shipping_hidden_tax_amount'
    ];

    /**
     * @var OrderBuilder|\Magento\Framework\Api\Builder
     */
    protected $orderBuilder;

    public function __construct(
        OrderBuilder $orderBuilder
    ) {
        $this->orderBuilder = $orderBuilder;
    }

    /**
     * @param array $data
     * @return OrderInterface
     */
    public function convert(\Magento\Quote\Model\Quote\Address $object, $data = [])
    {
        return $this->orderBuilder
            ->populateWithArray(array_merge(array_intersect_key($object->getData(), array_flip($this->fields)), $data))
            ->setCustomerEmail($object->getEmail())
            ->setCustomerPrefix($object->getPrefix())
            ->setCustomerFirstname($object->getFirstname())
            ->setCustomerMiddlename($object->getMiddlename())
            ->setCustomerLastname($object->getLastname())
            ->setCustomerSuffix($object->getSuffix())
            ->create();
    }
}
