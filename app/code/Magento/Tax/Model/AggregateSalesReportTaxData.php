<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Tax\Model\ResourceModel\Report\Tax as ResourceReportTax;
use Magento\Tax\Model\ResourceModel\Report\TaxFactory;

class AggregateSalesReportTaxData
{
    /**
     * @param TimezoneInterface $localeDate
     * @param TaxFactory $reportTaxFactory
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        protected readonly TimezoneInterface $localeDate,
        protected readonly TaxFactory $reportTaxFactory,
        protected readonly ResolverInterface $localeResolver
    ) {
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
        /** @var ResourceReportTax $reportTax */
        $reportTax = $this->reportTaxFactory->create();
        $reportTax->aggregate($date);
        $this->localeResolver->revert();
        return $this;
    }
}
