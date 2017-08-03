<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model;

/**
 * Class \Magento\Shipping\Model\Observer
 *
 * @since 2.0.0
 */
class Observer
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     * @since 2.0.0
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\ShippingFactory
     * @since 2.0.0
     */
    protected $_shippingFactory;

    /**
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Sales\Model\ResourceModel\Report\ShippingFactory $shippingFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Sales\Model\ResourceModel\Report\ShippingFactory $shippingFactory
    ) {
        $this->_localeResolver = $localeResolver;
        $this->_shippingFactory = $shippingFactory;
    }

    /**
     * Refresh sales shipment report statistics for last day
     *
     * @return $this
     * @since 2.0.0
     */
    public function aggregateSalesReportShipmentData()
    {
        $this->_localeResolver->emulate(0);
        $currentDate = new \DateTime();
        $date = $currentDate->modify('-25 hours');
        $this->_shippingFactory->create()->aggregate($date);
        $this->_localeResolver->revert();
        return $this;
    }
}
