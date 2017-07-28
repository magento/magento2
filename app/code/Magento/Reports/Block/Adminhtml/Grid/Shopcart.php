<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Grid;

/**
 * Adminhtml shopping carts report grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Shopcart extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Stores current currency code
     *
     * @var array
     * @since 2.0.0
     */
    protected $_currentCurrencyCode = null;

    /**
     * Ids of current stores
     *
     * @var array
     * @since 2.0.0
     */
    protected $_storeIds = [];

    /**
     * StoreIds setter
     * @codeCoverageIgnore
     *
     * @param array $storeIds
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getCurrentCurrencyCode()
    {
        if ($this->_currentCurrencyCode === null) {
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
     * @since 2.0.0
     */
    public function getRate($toCurrency)
    {
        return $this->_storeManager->getStore()->getBaseCurrency()->getRate($toCurrency);
    }
}
