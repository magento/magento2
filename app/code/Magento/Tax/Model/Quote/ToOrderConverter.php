<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Quote;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Quote\Model\Quote\Address\ToOrder as QuoteAddressToOrder;
use Magento\Quote\Model\Quote\Address as QuoteAddress;

class ToOrderConverter
{
    /**
     * @var QuoteAddress
     */
    protected $quoteAddress;

    /**
     * @var \Magento\Sales\Api\Data\OrderExtensionFactory
     */
    protected $orderExtensionFactory;

    /**
     * @param \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory
     */
    public function __construct(
        \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    /**
     * @param QuoteAddressToOrder $subject
     * @param QuoteAddress $address
     * @param array $additional
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeConvert(QuoteAddressToOrder $subject, QuoteAddress $address, $additional = [])
    {
        $this->quoteAddress = $address;
        return [$address, $additional];
    }

    /**
     * @param QuoteAddressToOrder $subject
     * @param OrderInterface $order
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterConvert(QuoteAddressToOrder $subject, OrderInterface $order)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $taxes = $this->quoteAddress->getAppliedTaxes();
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes == null) {
            $extensionAttributes = $this->orderExtensionFactory->create();
        }
        if (!empty($taxes)) {
            $extensionAttributes->setAppliedTaxes($taxes);
            $extensionAttributes->setConvertingFromQuote(true);
        }

        $itemAppliedTaxes = $this->quoteAddress->getItemsAppliedTaxes();
        if (!empty($itemAppliedTaxes)) {
            $extensionAttributes->setItemAppliedTaxes($itemAppliedTaxes);
        }
        $order->setExtensionAttributes($extensionAttributes);
        return $order;
    }
}
