<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Dashboard;

use Magento\Directory\Model\Currency;
use Magento\Store\Model\Store;

/**
 * Adminhtml dashboard bar block
 */
class Bar extends \Magento\Backend\Block\Dashboard\AbstractDashboard
{
    /**
     * @var array
     */
    protected $_totals = [];

    /**
     * @var Currency|null
     */
    protected $_currentCurrencyCode = null;

    /**
     * @var Currency
     */
    private $_currency;

    /**
     * Get totals
     *
     * @return array
     */
    public function getTotals()
    {
        return $this->_totals;
    }

    /**
     * Add total
     *
     * @param string $label
     * @param float $value
     * @param bool $isQuantity
     * @return $this
     */
    public function addTotal($label, $value, $isQuantity = false)
    {
        if (!$isQuantity) {
            $value = $this->format($value);
        }
        $decimals = '';
        $this->_totals[] = ['label' => $label, 'value' => $value, 'decimals' => $decimals];

        return $this;
    }

    /**
     * Formatting value specific for this store
     *
     * @param float $price
     * @return string
     */
    public function format($price)
    {
        return $this->getCurrency()->format($price);
    }

    /**
     * Setting currency model
     *
     * @param Currency $currency
     * @return void
     */
    public function setCurrency($currency)
    {
        $this->_currency = $currency;
    }

    /**
     * Retrieve currency model if not set then return currency model for current store
     *
     * @return Currency
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    public function getCurrency()
    {
        if ($this->_currentCurrencyCode === null) {
            if ($this->getRequest()->getParam('store')) {
                $this->_currentCurrencyCode = $this->_storeManager->getStore(
                    $this->getRequest()->getParam('store')
                )->getBaseCurrency();
            } elseif ($this->getRequest()->getParam('website')) {
                $this->_currentCurrencyCode = $this->_storeManager->getWebsite(
                    $this->getRequest()->getParam('website')
                )->getBaseCurrency();
            } elseif ($this->getRequest()->getParam('group')) {
                $this->_currentCurrencyCode = $this->_storeManager->getGroup(
                    $this->getRequest()->getParam('group')
                )->getWebsite()->getBaseCurrency();
            } else {
                $this->_currentCurrencyCode = $this->_storeManager->getStore(Store::DEFAULT_STORE_ID)
                    ->getBaseCurrency();
            }
        }

        return $this->_currentCurrencyCode;
    }
}
