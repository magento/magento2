<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Cron;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\SalesRule\Model\ResourceModel\Report\Rule as ReportRule;

class AggregateSalesReportCouponsData
{
    /**
     * @var ReportRule
     */
    protected $_reportRule;

    /**
     * @var ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @param ReportRule $reportRule
     * @param ResolverInterface $localeResolver
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        ReportRule $reportRule,
        ResolverInterface $localeResolver,
        TimezoneInterface $localeDate
    ) {
        $this->_reportRule = $reportRule;
        $this->_localeResolver = $localeResolver;
        $this->_localeDate = $localeDate;
    }

    /**
     * Refresh sales coupons report statistics for last day
     *
     * @return $this
     */
    public function execute()
    {
        $this->_localeResolver->emulate(0);
        $currentDate = $this->_localeDate->date();
        $date = $currentDate->modify('-25 hours');
        $this->_reportRule->aggregate($date);
        $this->_localeResolver->revert();

        return $this;
    }
}
