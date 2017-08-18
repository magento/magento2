<?php
/**
 * Google AdWords module observer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAdwords\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\GoogleAdwords\Observer\SetConversionValueObserver
 *
 */
class SetConversionValueObserver implements ObserverInterface
{
    /**
     * @var \Magento\GoogleAdwords\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $_collection;

    /**
     * Constructor
     *
     * @param \Magento\GoogleAdwords\Helper\Data $helper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $collection
     */
    public function __construct(
        \Magento\GoogleAdwords\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\ResourceModel\Order\Collection $collection
    ) {
        $this->_helper = $helper;
        $this->_collection = $collection;
        $this->_registry = $registry;
    }

    /**
     * Set base grand total of order to registry
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\GoogleAdwords\Observer\SetConversionValueObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!($this->_helper->isGoogleAdwordsActive() && $this->_helper->isDynamicConversionValue())) {
            return $this;
        }
        $orderIds = $observer->getEvent()->getOrderIds();
        if (!$orderIds || !is_array($orderIds)) {
            return $this;
        }
        $this->_collection->addFieldToFilter('entity_id', ['in' => $orderIds]);

        $conversionValue = 0;
        $conversionCurrency = false;
        $orderHasSendCurrency = $this->_helper->hasSendCurrency();
        foreach ($this->_collection as $order) {
            /** @var $order \Magento\Sales\Model\Order */
            $conversionValue += $orderHasSendCurrency ? $order->getGrandTotal() : $order->getBaseGrandTotal();
            $conversionCurrency = $orderHasSendCurrency ? $order->getOrderCurrencyCode() : false;
        }
        $this->_registry->register(
            \Magento\GoogleAdwords\Helper\Data::CONVERSION_VALUE_CURRENCY_REGISTRY_NAME,
            $conversionCurrency
        );
        $this->_registry->register(
            \Magento\GoogleAdwords\Helper\Data::CONVERSION_VALUE_REGISTRY_NAME,
            $conversionValue
        );
        return $this;
    }
}
