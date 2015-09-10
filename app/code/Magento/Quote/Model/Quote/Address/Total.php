<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address;

class Total extends \Magento\Framework\DataObject
{
    /**
     * @var array
     */
    protected $totalAmounts;

    /**
     * @var array
     */
    protected $baseTotalAmounts;

    /**
     * Merge numeric total values
     *
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function merge(\Magento\Quote\Model\Quote\Address\Total $total)
    {
        $newData = $total->getData();
        foreach ($newData as $key => $value) {
            if (is_numeric($value)) {
                $this->setData($key, $this->_getData($key) + $value);
            }
        }
        return $this;
    }

    /**
     * Set total amount value
     *
     * @param   string $code
     * @param   float $amount
     * @return $this
     */
    public function setTotalAmount($code, $amount)
    {
        $this->totalAmounts[$code] = $amount;
        if ($code != 'subtotal') {
            $code = $code . '_amount';
        }
        $this->setData($code, $amount);

        return $this;
    }

    /**
     * Set total amount value in base store currency
     *
     * @param   string $code
     * @param   float $amount
     * @return $this
     */
    public function setBaseTotalAmount($code, $amount)
    {
        $this->baseTotalAmounts[$code] = $amount;
        if ($code != 'subtotal') {
            $code = $code . '_amount';
        }
        $this->setData('base_' . $code, $amount);

        return $this;
    }

    /**
     * Add amount total amount value
     *
     * @param   string $code
     * @param   float $amount
     * @return $this
     */
    public function addTotalAmount($code, $amount)
    {
        $amount = $this->getTotalAmount($code) + $amount;
        $this->setTotalAmount($code, $amount);

        return $this;
    }

    /**
     * Add amount total amount value in base store currency
     *
     * @param   string $code
     * @param   float $amount
     * @return $this
     */
    public function addBaseTotalAmount($code, $amount)
    {
        $amount = $this->getBaseTotalAmount($code) + $amount;
        $this->setBaseTotalAmount($code, $amount);

        return $this;
    }

    /**
     * Get total amount value by code
     *
     * @param   string $code
     * @return  float|int
     */
    public function getTotalAmount($code)
    {
        if (isset($this->totalAmounts[$code])) {
            return $this->totalAmounts[$code];
        }

        return 0;
    }

    /**
     * Get total amount value by code in base store currency
     *
     * @param   string $code
     * @return  float|int
     */
    public function getBaseTotalAmount($code)
    {
        if (isset($this->baseTotalAmounts[$code])) {
            return $this->baseTotalAmounts[$code];
        }

        return 0;
    }

    /**
     * Get subtotal amount with applied discount in base currency
     *
     * @return float
     */
    public function getBaseSubtotalWithDiscount()
    {
        return $this->getBaseSubtotal() + $this->getBaseDiscountAmount();
    }

    /**
     * Get subtotal amount with applied discount
     *
     * @return float
     */
    public function getSubtotalWithDiscount()
    {
        return $this->getSubtotal() + $this->getDiscountAmount();
    }

    //@codeCoverageIgnoreStart
    /**
     * Get all total amount values
     *
     * @return array
     */
    public function getAllTotalAmounts()
    {
        return $this->totalAmounts;
    }

    /**
     * Get all total amount values in base currency
     *
     * @return array
     */
    public function getAllBaseTotalAmounts()
    {
        return $this->baseTotalAmounts;
    }
}
