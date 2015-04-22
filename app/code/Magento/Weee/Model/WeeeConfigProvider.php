<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Weee\Helper\Data as WeeeHelper;
use Magento\Weee\Model\Tax as WeeeDisplayConfig;
use Magento\Store\Model\StoreManagerInterface;

class WeeeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Weee\Helper\Data
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
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'iDisplayPriceWithWeeeDetails' => $this->iDisplayPriceWithWeeeDetails(),
            'isDisplayFinalPrice' => $this->isDisplayFinalPrice(),
            'isWeeeEnabled' => $this->isWeeeEnabled(),
            'getIncludeWeeeFlag' => $this->getIncludeWeeeFlag()
        ];
    }

    /**
     * @return int
     */
    private function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Whether to display weee details together with price
     *
     * @return bool
     */
    public function iDisplayPriceWithWeeeDetails()
    {
        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return false;
        }

        $displayWeeeDetails = $this->weeeHelper->typeOfDisplay(
            [WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_EXCL_DESCR_INCL],
            'cart',
            $this->storeManager->getStore()->getId()
        );
        if (!$displayWeeeDetails) {
            return false;
        }
        return true;
    }


    /**
     * Whether to display final price that include Weee amounts
     *
     * @return bool
     */
    public function isDisplayFinalPrice()
    {
        $flag = $this->weeeHelper->typeOfDisplay(
            WeeeDisplayConfig::DISPLAY_EXCL_DESCR_INCL,
            'cart',//$this->getZone(),
            $this->storeManager->getStore()->getId()
        );

        if (!$flag) {
            return false;
        }

        return true;
    }

    /**
     * Check if fixed taxes are used in system
     *
     * @return  bool
     */
    public function isWeeeEnabled()
    {
        return $this->weeeHelper->isEnabled($this->storeManager->getStore()->getId());
    }

    /**
     * Return the flag whether to include weee in the price
     *
     * @return bool|int
     */
    public function getIncludeWeeeFlag()
    {
        $includeWeee = $this->weeeHelper->typeOfDisplay(
            [WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL],
            'cart',//$this->getZone(),
            $this->getStoreId()
        );
        return $includeWeee;
    }
}
