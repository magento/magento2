<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Cron;

/**
 * Class \Magento\SalesRule\Cron\AggregateSalesReportCouponsData
 *
 */
class AggregateSalesReportCouponsData
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Report\Rule
     */
    protected $_reportRule;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @param \Magento\SalesRule\Model\ResourceModel\Report\Rule $reportRule
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     */
    public function __construct(
        \Magento\SalesRule\Model\ResourceModel\Report\Rule $reportRule,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
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
