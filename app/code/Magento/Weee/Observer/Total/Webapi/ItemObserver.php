<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Observer\Total\Webapi;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Weee\Helper\Data as WeeeHelper;
use Magento\Weee\Model\Tax as WeeeDisplayConfig;
use Magento\Framework\Event\ObserverInterface;

class ItemObserver implements ObserverInterface
{
    /**
     * @var WeeeHelper
     */
    protected $weeeHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param WeeeHelper $weeeHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        WeeeHelper $weeeHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->weeeHelper = $weeeHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Quote\Model\Quote\Item
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var  \Magento\Quote\Model\Quote\Item $item */
        $item = $observer->getEvent()->getItem();

        $item->setRowTotal($this->getRowTotal($item))
            ->setRowTotalInclTax($this->getRowTotalInclTax($item))
            ->setPrice($this->getUnitDisplayPriceExclTax($item))
            ->setPriceInclTax($this->getUnitDisplayPriceInclTax($item));

        return $item;
    }

    /**
     * Get display price for unit price including tax. The Weee amount will be added to unit price including tax
     * depending on Weee display setting
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return float
     */
    private function getUnitDisplayPriceInclTax($item)
    {
        $priceInclTax = $item->getPriceInclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $priceInclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            return $priceInclTax + $this->weeeHelper->getWeeeTaxInclTax($item);
        }

        return $priceInclTax;
    }

    /**
     * Get display price for row total excluding tax. The Weee amount will be added to row total
     * depending on Weee display setting
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return float
     */
    private function getRowTotal($item)
    {
        $rowTotalExclTax = $item->getRowTotal();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $rowTotalExclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            return $rowTotalExclTax + $item->getWeeeTaxAppliedRowAmount();
        }

        return $rowTotalExclTax;
    }

    /**
     * Get display price for row total including tax. The Weee amount will be added to row total including tax
     * depending on Weee display setting
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return float
     */
    private function getRowTotalInclTax($item)
    {
        $rowTotalInclTax = $item->getRowTotalInclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $rowTotalInclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            return $rowTotalInclTax + $this->weeeHelper->getRowWeeeTaxInclTax($item);
        }

        return $rowTotalInclTax;
    }

    /**
     * Get display price for unit price excluding tax. The Weee amount will be added to unit price
     * depending on Weee display setting
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return float
     */
    private function getUnitDisplayPriceExclTax($item)
    {
        $priceExclTax = $item->getCalculationPrice();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $priceExclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            return $priceExclTax + $item->getWeeeTaxAppliedAmount();
        }

        return $priceExclTax;
    }

    /**
     * Return the flag whether to include weee in the price
     *
     * @return bool|int
     */
    private function getIncludeWeeeFlag()
    {
        $includeWeee = $this->weeeHelper->typeOfDisplay(
            [WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL],
            'cart',
            $this->getStoreId()
        );
        return $includeWeee;
    }

    /**
     * @return int|null|string
     */
    private function getStoreId()
    {
        return $this->storeManager->getStore()->getStoreId();
    }
}
