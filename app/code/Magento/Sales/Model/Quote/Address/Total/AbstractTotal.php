<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Quote\Address\Total;

/**
 * Sales Quote Address Total  abstract model
 */
abstract class AbstractTotal
{
    /**
     * Total Code name
     *
     * @var string
     */
    protected $_code;

    /**
     * @var string
     */
    protected $_address = null;

    /**
     * Various abstract abilities
     *
     * @var bool
     */
    protected $_canAddAmountToAddress = true;

    /**
     * Various abstract abilities
     *
     * @var bool
     */
    protected $_canSetAddressAmount = true;

    /**
     * Key for item row total getting
     *
     * @var string
     */
    protected $_itemRowTotalKey = null;

    /**
     * Set total code code name
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->_code = $code;
        return $this;
    }

    /**
     * Retrieve total code name
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Label getter
     *
     * @return string
     */
    public function getLabel()
    {
        return '';
    }

    /**
     * Collect totals process.
     *
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Quote\Address $address)
    {
        $this->_setAddress($address);
        /**
         * Reset amounts
         */
        $this->_setAmount(0);
        $this->_setBaseAmount(0);
        return $this;
    }

    /**
     * Fetch (Retrieve data as array)
     *
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return array
     */
    public function fetch(\Magento\Sales\Model\Quote\Address $address)
    {
        $this->_setAddress($address);
        return [];
    }

    /**
     * Set address which can be used inside totals calculation
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return $this
     */
    protected function _setAddress(\Magento\Sales\Model\Quote\Address $address)
    {
        $this->_address = $address;
        return $this;
    }

    /**
     * Get quote address object
     *
     * @return  \Magento\Sales\Model\Quote\Address
     * @throws   \Magento\Framework\Model\Exception if address not declared
     */
    protected function _getAddress()
    {
        if ($this->_address === null) {
            throw new \Magento\Framework\Model\Exception(__('The address model is not defined.'));
        }
        return $this->_address;
    }

    /**
     * Set total model amount value to address
     *
     * @param   float $amount
     * @return $this
     */
    protected function _setAmount($amount)
    {
        if ($this->_canSetAddressAmount) {
            $this->_getAddress()->setTotalAmount($this->getCode(), $amount);
        }
        return $this;
    }

    /**
     * Set total model base amount value to address
     *
     * @param float $baseAmount
     * @internal param float $amount
     * @return $this
     */
    protected function _setBaseAmount($baseAmount)
    {
        if ($this->_canSetAddressAmount) {
            $this->_getAddress()->setBaseTotalAmount($this->getCode(), $baseAmount);
        }
        return $this;
    }

    /**
     * Add total model amount value to address
     *
     * @param   float $amount
     * @return $this
     */
    protected function _addAmount($amount)
    {
        if ($this->_canAddAmountToAddress) {
            $this->_getAddress()->addTotalAmount($this->getCode(), $amount);
        }
        return $this;
    }

    /**
     * Add total model base amount value to address
     *
     * @param float $baseAmount
     * @return $this
     */
    protected function _addBaseAmount($baseAmount)
    {
        if ($this->_canAddAmountToAddress) {
            $this->_getAddress()->addBaseTotalAmount($this->getCode(), $baseAmount);
        }
        return $this;
    }

    /**
     * Get all items
     *
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return array
     */
    protected function _getAddressItems(\Magento\Sales\Model\Quote\Address $address)
    {
        return $address->getAllItems();
    }

    /**
     * Getter for row default total
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return float|int
     */
    public function getItemRowTotal(\Magento\Sales\Model\Quote\Item\AbstractItem $item)
    {
        if (!$this->_itemRowTotalKey) {
            return 0;
        }
        return $item->getDataUsingMethod($this->_itemRowTotalKey);
    }

    /**
     * Getter for row default base total
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return float|int
     */
    public function getItemBaseRowTotal(\Magento\Sales\Model\Quote\Item\AbstractItem $item)
    {
        if (!$this->_itemRowTotalKey) {
            return 0;
        }
        return $item->getDataUsingMethod('base_' . $this->_itemRowTotalKey);
    }

    /**
     * Whether the item row total may be compounded with others
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return bool
     */
    public function getIsItemRowTotalCompoundable(\Magento\Sales\Model\Quote\Item\AbstractItem $item)
    {
        if ($item->getData("skip_compound_{$this->_itemRowTotalKey}")) {
            return false;
        }
        return true;
    }

    /**
     * Process model configuration array.
     * This method can be used for changing models apply sort order
     *
     * @param   array $config
     * @param   store $store
     * @return  array
     */
    public function processConfigArray($config, $store)
    {
        return $config;
    }
}
