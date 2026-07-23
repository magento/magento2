<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 *  Set value for Special Price start date
 */
class SetSpecialPriceStartDate implements ObserverInterface
{
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @param TimezoneInterface $localeDate
     */
    public function __construct(TimezoneInterface $localeDate)
    {
        $this->localeDate = $localeDate;
    }

    /**
     * Set the current date to Special Price From attribute if it's empty.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var  $product \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getProduct();
        if ($product->getSpecialPrice() && $product->getSpecialFromDate() === null) {
            // Set the special_from_date to the current date with time 00:00:00 when a special price is defined
            // but no start date is specified. This ensures the special price takes effect immediately
            // and is consistent with how the special price validation works in Magento.
            // The time is explicitly set to midnight to ensure the special price is active for the entire day.
            $product->setData(
                'special_from_date',
                $this->localeDate->date()->setTime(0, 0)->format('Y-m-d H:i:s')
            );
        }
        return $this;
    }
}
