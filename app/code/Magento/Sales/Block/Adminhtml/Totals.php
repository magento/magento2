<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml;

use Magento\Framework\DataObject;

/**
 * Adminhtml sales totals block
 */
class Totals extends \Magento\Sales\Block\Order\Totals
{
    /**
     * Admin helper
     *
     * @var \Magento\Sales\Helper\Admin
     */
    protected $_adminHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        $this->_adminHelper = $adminHelper;
        parent::__construct($context, $registry, $data);
    }

    /**
     * Format total value based on order currency
     *
     * @param \Magento\Framework\DataObject $total
     * @return string
     */
    public function formatValue($total)
    {
        if (!$total->getIsFormated()) {
            return $this->_adminHelper->displayPrices($this->getOrder(), $total->getBaseValue(), $total->getValue());
        }
        return $total->getValue();
    }

    /**
     * Initialize order totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        $this->_totals = [];
        $order = $this->getSource();

        $this->_totals['subtotal'] = new DataObject(
            [
                'code' => 'subtotal',
                'value' => $order->getSubtotal(),
                'base_value' => $order->getBaseSubtotal(),
                'label' => __('Subtotal'),
            ]
        );

        /**
         * Add discount
         */
        if ((double)$order->getDiscountAmount() != 0) {
            if ($order->getDiscountDescription()) {
                $discountLabel = __('Discount (%1)', $order->getDiscountDescription());
            } else {
                $discountLabel = __('Discount');
            }
            $this->_totals['discount'] = new DataObject(
                [
                    'code' => 'discount',
                    'value' => $order->getDiscountAmount(),
                    'base_value' => $order->getBaseDiscountAmount(),
                    'label' => $discountLabel,
                ]
            );
        }

        /**
         * Add shipping
         */
        if (!$order->getIsVirtual()
            && ((double)$order->getShippingAmount()
            || $order->getShippingDescription())
        ) {
            $shippingLabel = __('Shipping & Handling');

            if ($order->getCouponCode() && !isset($this->_totals['discount'])) {
                $shippingLabel .= " ({$order->getCouponCode()})";
            }

            $this->_totals['shipping'] = new DataObject(
                [
                    'code' => 'shipping',
                    'value' => $order->getShippingAmount(),
                    'base_value' => $order->getBaseShippingAmount(),
                    'label' => $shippingLabel,
                ]
            );
        }

        $this->_totals['grand_total'] = new DataObject(
            [
                'code' => 'grand_total',
                'strong' => true,
                'value' => $order->getGrandTotal(),
                'base_value' => $order->getBaseGrandTotal(),
                'label' => __('Grand Total'),
                'area' => 'footer',
            ]
        );

        return $this;
    }
}
