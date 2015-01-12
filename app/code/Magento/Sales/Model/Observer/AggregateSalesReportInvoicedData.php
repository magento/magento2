<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Observer;

/**
 * Class AggregateSalesReportInvoicedData
 */
class AggregateSalesReportInvoicedData
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Sales\Model\Resource\Report\InvoicedFactory
     */
    protected $invoicedFactory;

    /**
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Sales\Model\Resource\Report\InvoicedFactory $invoicedFactory
     */
    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Sales\Model\Resource\Report\InvoicedFactory $invoicedFactory
    ) {
        $this->localeResolver = $localeResolver;
        $this->localeDate = $timezone;
        $this->invoicedFactory = $invoicedFactory;
    }

    /**
     * Refresh sales invoiced report statistics for last day
     *
     * @return void
     */
    public function execute()
    {
        $this->localeResolver->emulate(0);
        $currentDate = $this->localeDate->date();
        $date = $currentDate->subHour(25);
        $this->invoicedFactory->create()->aggregate($date);
        $this->localeResolver->revert();
    }
}
