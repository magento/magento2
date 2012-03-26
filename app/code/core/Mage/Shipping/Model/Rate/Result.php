<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Shipping
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Shipping_Model_Rate_Result
{
    /**
     * Shippin method rates
     *
     * @var array
     */
    protected $_rates = array();

    /**
     * Shipping errors
     *
     * @var null|bool
     */
    protected $_error = null;

    /**
     * Reset result
     *
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function reset()
    {
        $this->_rates = array();
        return $this;
    }

    /**
     * Set Error
     *
     * @param bool $error
     * @return void
     */
    public function setError($error)
    {
        $this->_error = $error;
    }

    /**
     * Get Error
     *
     * @return null|bool;
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * Add a rate to the result
     *
     * @param Mage_Shipping_Model_Rate_Result_Abstract|Mage_Shipping_Model_Rate_Result $result
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function append($result)
    {
        if ($result instanceof Mage_Shipping_Model_Rate_Result_Error) {
            $this->setError(true);
        }
        if ($result instanceof Mage_Shipping_Model_Rate_Result_Abstract) {
            $this->_rates[] = $result;
        }
        elseif ($result instanceof Mage_Shipping_Model_Rate_Result) {
            $rates = $result->getAllRates();
            foreach ($rates as $rate) {
                $this->append($rate);
            }
        }
        return $this;
    }

    /**
     * Return all quotes in the result
     *
     * @return array
     */
    public function getAllRates()
    {
        return $this->_rates;
    }

    /**
     * Return rate by id in array
     *
     * @param int $id
     * @return Mage_Shipping_Model_Rate_Result_Method|null
     */
    public function getRateById($id)
    {
        return isset($this->_rates[$id]) ? $this->_rates[$id] : null;
    }

    /**
     * Return quotes for specified type
     *
     * @param string $carrier
     * @return array
     */
    public function getRatesByCarrier($carrier)
    {
        $result = array();
        foreach ($this->_rates as $rate) {
            if ($rate->getCarrier() === $carrier) {
                $result[] = $rate;
            }
        }
        return $result;
    }

    /**
     * Converts object to array
     *
     * @return array
     */
    public function asArray()
    {
        $currencyFilter = Mage::app()->getStore()->getPriceFilter();
        $rates = array();
        $allRates = $this->getAllRates();
        foreach ($allRates as $rate) {
            $rates[$rate->getCarrier()]['title'] = $rate->getCarrierTitle();
            $rates[$rate->getCarrier()]['methods'][$rate->getMethod()] = array(
                'title' => $rate->getMethodTitle(),
                'price' => $rate->getPrice(),
                'price_formatted' => $currencyFilter->filter($rate->getPrice()),
            );
        }
        return $rates;
    }

    /**
     * Get cheapest rate
     *
     * @return null|Mage_Shipping_Model_Rate_Result_Method
     */
    public function getCheapestRate()
    {
        $cheapest = null;
        $minPrice = 100000;
        foreach ($this->getAllRates() as $rate) {
            if (is_numeric($rate->getPrice()) && $rate->getPrice() < $minPrice) {
                $cheapest = $rate;
                $minPrice = $rate->getPrice();
            }
        }
        return $cheapest;
    }

    /**
     * Sort rates by price from min to max
     *
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function sortRatesByPrice()
    {
        if (!is_array($this->_rates) || !count($this->_rates)) {
            return $this;
        }
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */
        foreach ($this->_rates as $i => $rate) {
            $tmp[$i] = $rate->getPrice();
        }

        natsort($tmp);

        foreach ($tmp as $i => $price) {
            $result[] = $this->_rates[$i];
        }

        $this->reset();
        $this->_rates = $result;
        return $this;
    }

    /**
     * Set price for each rate according to count of packages
     *
     * @param int $packageCount
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function updateRatePrice($packageCount)
    {
        if ($packageCount > 1) {
            foreach ($this->_rates as $rate) {
                $rate->setPrice($rate->getPrice() * $packageCount);
            }
        }

        return $this;
    }
}
