<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\CronJob;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class AggregateSalesReportBestsellersData
 */
class AggregateSalesReportBestsellersData
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
     * @var \Magento\Sales\Model\ResourceModel\Report\BestsellersFactory
     */
    protected $bestsellersFactory;

    /**
     * @param ResolverInterface $localeResolver
     * @param TimezoneInterface $timezone
     * @param \Magento\Sales\Model\ResourceModel\Report\BestsellersFactory $bestsellersFactory
     */
    public function __construct(
        ResolverInterface $localeResolver,
        TimezoneInterface $timezone,
        \Magento\Sales\Model\ResourceModel\Report\BestsellersFactory $bestsellersFactory
    ) {
        $this->localeResolver = $localeResolver;
        $this->localeDate = $timezone;
        $this->bestsellersFactory = $bestsellersFactory;
    }

    /**
     * Refresh bestsellers report statistics for last day
     *
     * @return void
     */
    public function execute()
    {
        $this->localeResolver->emulate(0);
        $currentDate = $this->localeDate->date();
        $date = $currentDate->sub(new \DateInterval('PT25H'));
        $this->bestsellersFactory->create()->aggregate($date);
        $this->localeResolver->revert();
    }
}
