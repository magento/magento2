<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Copy custom fields from quote tables to order.
 */
class SalesEventQuoteSubmitBeforeObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\DataObject\Copy\Config
     */
    private $fieldsetConfig;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    private $order;

    /**
     * SalesEventQuoteSubmitBeforeObserver constructor.
     *
     * @param \Magento\Framework\DataObject\Copy\Config $fieldsetConfig
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    public function __construct(
        \Magento\Framework\DataObject\Copy\Config $fieldsetConfig,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $this->fieldsetConfig = $fieldsetConfig;
        $this->order = $order;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Quote\Api\Data\CartInterface $quote */
        $quote = $observer->getEvent()->getQuote();

        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $observer->getEvent()->getOrder();

        $fields = $this->fieldsetConfig->getFieldset('sales_convert_quote', 'global');

        $methods = get_class_methods($this->order);

        foreach ($fields as $code => $node) {
            $targetCode = (string)$node['to_order'];
            $targetCode = $targetCode == '*' ? $code : $targetCode;

            if (!in_array($this->getMethodName($targetCode), $methods)) {
                $code2 = $quote->getData($code);
                $order->setData($targetCode, $code2);
            }
        }

        return $this;
    }

    /**
     * Convert key to method name.
     *
     * @param string $key
     *
     * @return string
     */
    private function getMethodName($key)
    {
        return 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
    }
}
