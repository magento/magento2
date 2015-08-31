<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Observer;

class AggregateSalesReportTaxData
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Tax\Model\Resource\Report\TaxFactory
     */
    protected $reportTaxFactory;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Tax\Model\Resource\Report\TaxFactory $reportTaxFactory
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Tax\Model\Resource\Report\TaxFactory $reportTaxFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver
    ) {
        $this->localeDate = $localeDate;
        $this->reportTaxFactory = $reportTaxFactory;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Refresh sales tax report statistics for last day
     *
     * @param \Magento\Cron\Model\Schedule $schedule
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function invoke($schedule)
    {
        $this->localeResolver->emulate(0);
        $currentDate = $this->localeDate->date();
        $date = $currentDate->modify('-25 hours');
        /** @var $reportTax \Magento\Tax\Model\Resource\Report\Tax */
        $reportTax = $this->reportTaxFactory->create();
        $reportTax->aggregate($date);
        $this->localeResolver->revert();
        return $this;
    }
}
