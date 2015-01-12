<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model;

class Observer
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Sales\Model\Resource\Report\ShippingFactory
     */
    protected $_shippingFactory;

    /**
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Sales\Model\Resource\Report\ShippingFactory $shippingFactory
     */
    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Sales\Model\Resource\Report\ShippingFactory $shippingFactory
    ) {
        $this->_localeResolver = $localeResolver;
        $this->_shippingFactory = $shippingFactory;
    }

    /**
     * Refresh sales shipment report statistics for last day
     *
     * @return $this
     */
    public function aggregateSalesReportShipmentData()
    {
        $this->_localeResolver->emulate(0);
        $currentDate = $this->_coreLocale->date();
        $date = $currentDate->subHour(25);
        $this->_shippingFactory->create()->aggregate($date);
        $this->_localeResolver->revert();
        return $this;
    }
}
