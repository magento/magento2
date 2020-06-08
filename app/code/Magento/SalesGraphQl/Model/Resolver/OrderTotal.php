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
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Tax\Item as TaxItem;
use Magento\SalesGraphQl\Model\Orders\GetDiscounts;
use Magento\SalesGraphQl\Model\Orders\GetTaxes;

class OrderTotal implements ResolverInterface
{
    /**
     * @var GetDiscounts
     */
    private $getDiscounts;

    /**
     * @var GetTaxes
     */
    private $getTaxes;

    /**
     * @var TaxItem
     */
    private $taxItem;

    /**
     * @param GetDiscounts $getDiscounts
     * @param GetTaxes $getTaxes
     * @param TaxItem $taxItem
     */
    public function __construct(
        GetDiscounts $getDiscounts,
        GetTaxes $getTaxes,
        TaxItem $taxItem
    ) {
        $this->getDiscounts = $getDiscounts;
        $this->getTaxes = $getTaxes;
        $this->taxItem = $taxItem;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        if (!isset($value['model']) && !($value['model'] instanceof Order)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Order $orderModel */
        $orderModel = $value['model'];

        $currency = $orderModel->getOrderCurrencyCode();
        /** @var TaxItem $taxModel */

        $taxModel = $value['model'];
        if (!empty($taxModel->getExtensionAttributes()->getAppliedTaxes())) {
            if (isset($taxModel->getExtensionAttributes()->getAppliedTaxes()[0])) {
                $appliedTaxes = $taxModel->getExtensionAttributes()->getAppliedTaxes()[0];
                $appliedTaxesArray = $appliedTaxes->getData();
            }
        } else {
            $appliedTaxesArray = [];
        }

        $total = [
                'base_grand_total' => ['value' => $orderModel->getBaseGrandTotal(), 'currency' => $currency],
                'grand_total' => ['value' => $orderModel->getGrandTotal(), 'currency' => $currency],
                'subtotal' => ['value' => $orderModel->getSubtotal(), 'currency' => $currency],
                'total_tax' => ['value' => $orderModel->getTaxAmount(), 'currency' => $currency],
                'taxes' => $this->getTaxes->execute($orderModel, $appliedTaxesArray),
                'discounts' => $this->getDiscounts->execute($orderModel),
                'total_shipping' => ['value' => $orderModel->getShippingAmount(), 'currency' => $currency],
                'shipping_handling' => [
                    'amount_excluding_tax' => ['value' => ($orderModel->getShippingInclTax() - $orderModel->getBaseShippingTaxAmount()), 'currency' => $currency],
                    'amount_including_tax' => ['value' => $orderModel->getShippingInclTax(), 'currency' => $currency],
                    'total_amount' => ['value' => $orderModel->getBaseShippingAmount(), 'currency' => $currency],
                    'taxes' => $this->getTaxes->execute($orderModel, $appliedTaxesArray),
                    'discounts' => $this->getShippingDiscountDetails($orderModel),
                ]
            ];
        return $total;
    }

    /**
     * Returns information about an applied discount
     *
     * @param Order $order
     * @return array|null
     */
    private function getShippingDiscountDetails(Order $order)
    {
        $discounts [] =
                [
                    'label' => $order->getDiscountDescription() ?? "null",
                    'amount' => [
                        'value' => $order->getShippingDiscountAmount(),
                        'currency' => $order->getOrderCurrencyCode()
                    ]
                ];
        return $discounts;
    }
}
