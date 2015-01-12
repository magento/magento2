<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Grid;

/**
 * Adminhtml shopping carts report grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Shopcart extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Stores current currency code
     *
     * @var array
     */
    protected $_currentCurrencyCode = null;

    /**
     * Ids of current stores
     *
     * @var array
     */
    protected $_storeIds = [];

    /**
     * StoreIds setter
     *
     * @param array $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    /**
     * Retrieve currency code based on selected store
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        if (is_null($this->_currentCurrencyCode)) {
            reset($this->_storeIds);
            $this->_currentCurrencyCode = count(
                $this->_storeIds
            ) > 0 ? $this->_storeManager->getStore(
                current($this->_storeIds)
            )->getBaseCurrencyCode() : $this->_storeManager->getStore()->getBaseCurrencyCode();
        }
        return $this->_currentCurrencyCode;
    }

    /**
     * Get currency rate (base to given currency)
     *
     * @param string|\Magento\Directory\Model\Currency $toCurrency
     * @return float
     */
    public function getRate($toCurrency)
    {
        return $this->_storeManager->getStore()->getBaseCurrency()->getRate($toCurrency);
    }
}
