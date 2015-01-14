<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Observer;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class AggregateSalesReportRefundedData
 */
class AggregateSalesReportRefundedData
{
    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Sales\Model\Resource\Report\RefundedFactory
     */
    protected $refundedFactory;

    /**
     * @param ResolverInterface $localeResolver
     * @param TimezoneInterface $timezone
     * @param \Magento\Sales\Model\Resource\Report\RefundedFactory $refundedFactory
     */
    public function __construct(
        ResolverInterface $localeResolver,
        TimezoneInterface $timezone,
        \Magento\Sales\Model\Resource\Report\RefundedFactory $refundedFactory
    ) {
        $this->localeResolver = $localeResolver;
        $this->localeDate = $timezone;
        $this->refundedFactory = $refundedFactory;
    }

    /**
     * Refresh sales refunded report statistics for last day
     *
     * @return void
     */
    public function execute()
    {
        $this->localeResolver->emulate(0);
        $currentDate = $this->localeDate->date();
        $date = $currentDate->subHour(25);
        $this->refundedFactory->create()->aggregate($date);
        $this->localeResolver->revert();
    }
}
