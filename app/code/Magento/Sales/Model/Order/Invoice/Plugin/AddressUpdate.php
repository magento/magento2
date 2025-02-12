<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Global configuration storage.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $globalConfig;

    /**
     * AddressUpdate constructor.
     * @param \Magento\Sales\Model\ResourceModel\GridPool $gridPool
     * @param \Magento\Sales\Model\ResourceModel\Attribute $attribute
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\GridPool $gridPool,
        \Magento\Sales\Model\ResourceModel\Attribute $attribute,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
    ) {
        $this->gridPool = $gridPool;
        $this->attribute = $attribute;
        $this->globalConfig = $globalConfig;
    }

    /**
     * Attach addresses to invoices
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\Handler\Address $subject
     * @param \Magento\Sales\Model\ResourceModel\Order\Handler\Address $result
     * @param \Magento\Sales\Model\Order $order
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
            if ($orderInvoiceHasChanges && !$this->globalConfig->getValue('dev/grid/async_indexing')) {
                $this->gridPool->refreshByOrderId($order->getId());
            }
        }
    }
}
