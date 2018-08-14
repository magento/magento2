<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address\Total;

/**
 * Sales Quote Address Total  abstract model
 *
 * @api
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @since 100.0.2
 */
abstract class AbstractTotal implements CollectorInterface, ReaderInterface
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
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $this->_setAddress($shippingAssignment->getShipping()->getAddress());
        $this->_setTotal($total);
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
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     * @internal param \Magento\Quote\Model\Quote\Address $address
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        return [];
    }

    /**
     * Set address which can be used inside totals calculation
     *
     * @param   \Magento\Quote\Model\Quote\Address $address
     * @return $this
     */
    protected function _setAddress(\Magento\Quote\Model\Quote\Address $address)
    {
        $this->_address = $address;
        return $this;
    }

    /**
     * Get quote address object
     *
     * @return  \Magento\Quote\Model\Quote\Address
     * @throws   \Magento\Framework\Exception\LocalizedException if address not declared
     */
    protected function _getAddress()
    {
        if ($this->_address === null) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The address model is not defined.'));
        }
        return $this->_address;
    }

    /**
     * @var \Magento\Quote\Model\Quote\Address\Total
     */
    protected $total;

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function _setTotal(\Magento\Quote\Model\Quote\Address\Total $total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Address\Total
     */
    protected function _getTotal()
    {
        return $this->total;
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
            $this->_getTotal()->setTotalAmount($this->getCode(), $amount);
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
            $this->_getTotal()->setBaseTotalAmount($this->getCode(), $baseAmount);
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
            $this->_getTotal()->addTotalAmount($this->getCode(), $amount);
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
            $this->_getTotal()->addBaseTotalAmount($this->getCode(), $baseAmount);
        }
        return $this;
    }

    /**
     * Get all items
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return array
     */
    protected function _getAddressItems(\Magento\Quote\Model\Quote\Address $address)
    {
        return $address->getAllItems();
    }

    /**
     * Getter for row default total
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return float|int
     */
    public function getItemRowTotal(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        if (!$this->_itemRowTotalKey) {
            return 0;
        }
        return $item->getDataUsingMethod($this->_itemRowTotalKey);
    }

    /**
     * Getter for row default base total
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return float|int
     */
    public function getItemBaseRowTotal(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        if (!$this->_itemRowTotalKey) {
            return 0;
        }
        return $item->getDataUsingMethod('base_' . $this->_itemRowTotalKey);
    }

    /**
     * Whether the item row total may be compounded with others
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsItemRowTotalCompoundable(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function processConfigArray($config, $store)
    {
        return $config;
    }
}
