<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Set value for Special Price start date
 *
 * Class SetSpecialPriceStartDate
 * @package Magento\Catalog\Observer
 */
class SetSpecialPriceStartDate implements ObserverInterface
{
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @param TimezoneInterface $localeDate
     * @codeCoverageIgnore
     */
    public function __construct(TimezoneInterface $localeDate)
    {
        $this->localeDate = $localeDate;
    }

    /**
     * Set the current date to Special Price From attribute if it empty
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getEvent()->getProduct();
        if ($product->getSpecialPrice() && !$product->getSpecialFromDate()) {
            $product->setData('special_from_date', $this->localeDate->date());
        }

        return $this;
    }
}
