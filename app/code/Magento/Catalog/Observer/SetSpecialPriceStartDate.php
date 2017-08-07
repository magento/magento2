<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 *  Set value for Special Price start date
 * @since 2.2.0
 */
class SetSpecialPriceStartDate implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     * @since 2.2.0
     */
    private $localeDate;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @codeCoverageIgnore
     * @since 2.2.0
     */
    public function __construct(\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate)
    {
        $this->localeDate = $localeDate;
    }

    /**
     * Set the current date to Special Price From attribute if it empty
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @since 2.2.0
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
