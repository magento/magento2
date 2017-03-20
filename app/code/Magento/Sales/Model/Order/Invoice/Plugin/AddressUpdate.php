<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Invoice\Plugin;

class AddressUpdate
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\GridPool
     */
    private $gridPool;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Attribute
     */
    private $attribute;

    /**
     * AddressUpdate constructor.
     * @param \Magento\Sales\Model\ResourceModel\GridPool $gridPool
     * @param \Magento\Sales\Model\ResourceModel\Attribute $attribute
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\GridPool $gridPool,
        \Magento\Sales\Model\ResourceModel\Attribute $attribute
    ) {
        $this->gridPool = $gridPool;
        $this->attribute = $attribute;
    }

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Handler\Address $subject
     * @param \Magento\Sales\Model\ResourceModel\Order\Handler\Address $result
     * @param \Magento\Sales\Model\Order $order
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterProcess(
        \Magento\Sales\Model\ResourceModel\Order\Handler\Address $subject,
        \Magento\Sales\Model\ResourceModel\Order\Handler\Address $result,
        \Magento\Sales\Model\Order $order
    ) {
        if ($order->hasInvoices()) {
            $billingAddress = $order->getBillingAddress();
            $shippingAddress = $order->getShippingAddress();

            $orderInvoiceHasChanges = false;
            /** @var \Magento\Sales\Model\Order\Invoice $invoice */
            foreach ($order->getInvoiceCollection()->getItems() as $invoice) {
                $invoiceAttributesForSave = [];

                if (!$invoice->getBillingAddressId() && $billingAddress) {
                    $invoice->setBillingAddressId($billingAddress->getId());
                    $invoiceAttributesForSave[] = 'billing_address_id';
                    $orderInvoiceHasChanges = true;
                }

                if (!$invoice->getShippingAddressId() && $shippingAddress) {
                    $invoice->setShippingAddressId($shippingAddress->getId());
                    $invoiceAttributesForSave[] = 'shipping_address_id';
                    $orderInvoiceHasChanges = true;
                }

                if (!empty($invoiceAttributesForSave)) {
                    $this->attribute->saveAttribute($invoice, $invoiceAttributesForSave);
                }
            }

            if ($orderInvoiceHasChanges) {
                $this->gridPool->refreshByOrderId($order->getId());
            }
        }
    }
}
