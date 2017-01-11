<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;

class SetSpecialPriceStartDateObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @codeCoverageIgnore
     */
    public function __construct(\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate)
    {
        $this->localeDate = $localeDate;
    }

    /**
     * Setting Special Price start date
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var  $product \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getProduct();
        if ($product->getSpecialPrice() && !$product->getSpecialFromDate()) {
            $product->setData('special_from_date', $this->localeDate->date());
        }

        return $this;
    }
}
