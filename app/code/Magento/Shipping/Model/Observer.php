<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model;

use DateTime;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Sales\Model\ResourceModel\Report\ShippingFactory;

class Observer
{
    /**
     * @var ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var ShippingFactory
     */
    protected $_shippingFactory;

    /**
     * @param ResolverInterface $localeResolver
     * @param ShippingFactory $shippingFactory
     */
    public function __construct(
        ResolverInterface $localeResolver,
        ShippingFactory $shippingFactory
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
        $currentDate = new DateTime();
        $date = $currentDate->modify('-25 hours');
        $this->_shippingFactory->create()->aggregate($date);
        $this->_localeResolver->revert();
        return $this;
    }
}
