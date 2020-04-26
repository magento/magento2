<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Quote\Address\Total;

use Magento\Quote\Api\Data\ShippingAssignmentInterface as ShippingAssignment;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\SalesRule\Model\Quote\Discount as DiscountCollector;
use Magento\SalesRule\Model\Validator;

/**
 * Total collector for shipping discounts.
 */
class ShippingDiscount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var Validator
     */
    private $calculator;

    /**
     * @param Validator $calculator
     */
    public function __construct(Validator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @inheritdoc
     *
     * @param Quote $quote
     * @param ShippingAssignment $shippingAssignment
     * @param Total $total
     * @return ShippingDiscount
     */
    public function collect(Quote $quote, ShippingAssignment $shippingAssignment, Total $total): self
    {
        parent::collect($quote, $shippingAssignment, $total);

        $address = $shippingAssignment->getShipping()->getAddress();
        $this->calculator->reset($address);

        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }

        $address->setShippingDiscountAmount(0);
        $address->setBaseShippingDiscountAmount(0);
        if ($total->getShippingAmountForDiscount() !== null) {
            $address->setShippingAmountForDiscount($total->getShippingAmountForDiscount());
            $address->setBaseShippingAmountForDiscount($total->getBaseShippingAmountForDiscount());
        }
        if ($address->getShippingAmount()) {
            $this->calculator->processShippingAmount($address);
            $total->addTotalAmount(DiscountCollector::COLLECTOR_TYPE_CODE, -$address->getShippingDiscountAmount());
            $total->addBaseTotalAmount(
                DiscountCollector::COLLECTOR_TYPE_CODE,
                -$address->getBaseShippingDiscountAmount()
            );
            $total->setShippingDiscountAmount($address->getShippingDiscountAmount());
            $total->setBaseShippingDiscountAmount($address->getBaseShippingDiscountAmount());

            $this->calculator->prepareDescription($address);
            $total->setDiscountDescription($address->getDiscountDescription());
            $total->setSubtotalWithDiscount($total->getSubtotal() + $total->getDiscountAmount());
            $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() + $total->getBaseDiscountAmount());

            $address->setDiscountAmount($total->getDiscountAmount());
            $address->setBaseDiscountAmount($total->getBaseDiscountAmount());
        }

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(Quote $quote, Total $total): array
    {
        $result = [];
        $amount = $total->getDiscountAmount();

        if ($amount != 0) {
            $description = (string)$total->getDiscountDescription() ?: '';
            $result = [
                'code' => DiscountCollector::COLLECTOR_TYPE_CODE,
                'title' => strlen($description) ? __('Discount (%1)', $description) : __('Discount'),
                'value' => $amount
            ];
        }
        return $result;
    }
}
