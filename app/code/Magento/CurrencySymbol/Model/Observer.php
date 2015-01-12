<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Currency Symbol Observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CurrencySymbol\Model;

class Observer
{
    /**
     * Currency symbol data
     *
     * @var \Magento\CurrencySymbol\Helper\Data
     */
    protected $_currencySymbolData = null;

    /**
     * @param \Magento\CurrencySymbol\Helper\Data $currencySymbolData
     */
    public function __construct(\Magento\CurrencySymbol\Helper\Data $currencySymbolData)
    {
        $this->_currencySymbolData = $currencySymbolData;
    }

    /**
     * Generate options for currency displaying with custom currency symbol
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function currencyDisplayOptions(\Magento\Framework\Event\Observer $observer)
    {
        $baseCode = $observer->getEvent()->getBaseCode();
        $currencyOptions = $observer->getEvent()->getCurrencyOptions();
        $currencyOptions->setData($this->_currencySymbolData->getCurrencyOptions($baseCode));

        return $this;
    }
}
