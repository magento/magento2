<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model;

/**
 * Class \Magento\Tax\Model\AggregateSalesReportTaxData
 *
 */
class AggregateSalesReportTaxData
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Tax\Model\ResourceModel\Report\TaxFactory
     */
    protected $reportTaxFactory;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Tax\Model\ResourceModel\Report\TaxFactory $reportTaxFactory
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Tax\Model\ResourceModel\Report\TaxFactory $reportTaxFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver
    ) {
        $this->localeDate = $localeDate;
        $this->reportTaxFactory = $reportTaxFactory;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Refresh sales tax report statistics for last day
     *
     * @return $this
     */
    public function invoke()
    {
        $this->localeResolver->emulate(0);
        $currentDate = $this->localeDate->date();
        $date = $currentDate->modify('-25 hours');
        /** @var $reportTax \Magento\Tax\Model\ResourceModel\Report\Tax */
        $reportTax = $this->reportTaxFactory->create();
        $reportTax->aggregate($date);
        $this->localeResolver->revert();
        return $this;
    }
}
