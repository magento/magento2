<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\CronJob;

/**
 * Class AggregateSalesReportInvoicedData
 * @since 2.0.0
 */
class AggregateSalesReportInvoicedData
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     * @since 2.0.0
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     * @since 2.0.0
     */
    protected $localeDate;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\InvoicedFactory
     * @since 2.0.0
     */
    protected $invoicedFactory;

    /**
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Sales\Model\ResourceModel\Report\InvoicedFactory $invoicedFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Sales\Model\ResourceModel\Report\InvoicedFactory $invoicedFactory
    ) {
        $this->localeResolver = $localeResolver;
        $this->localeDate = $timezone;
        $this->invoicedFactory = $invoicedFactory;
    }

    /**
     * Refresh sales invoiced report statistics for last day
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->localeResolver->emulate(0);
        $currentDate = $this->localeDate->date();
        $date = $currentDate->sub(new \DateInterval('PT25H'));
        $this->invoicedFactory->create()->aggregate($date);
        $this->localeResolver->revert();
    }
}
