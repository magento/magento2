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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Currency filter
 */
namespace Magento\Directory\Model\Currency;

class Filter implements \Zend_Filter_Interface
{
    /**
     * Rate value
     *
     * @var float
     */
    protected $_rate;

    /**
     * Currency object
     *
     * @var \Magento\Framework\CurrencyInterface
     */
    protected $_currency;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param string $code
     * @param int $rate
     */
    public function __construct(
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        $code,
        $rate = 1
    ) {
        $this->_localeFormat = $localeFormat;
        $this->_storeManager = $storeManager;
        $this->_currency = $localeCurrency->getCurrency($code);
        $this->_rate = $rate;
    }

    /**
     * Set filter rate
     *
     * @param float $rate
     * @return void
     */
    public function setRate($rate)
    {
        $this->_rate = $rate;
    }

    /**
     * Filter value
     *
     * @param float $value
     * @return string
     */
    public function filter($value)
    {
        $value = $this->_localeFormat->getNumber($value);
        $value = $this->_storeManager->getStore()->roundPrice($this->_rate * $value);
        $value = sprintf("%f", $value);
        return $this->_currency->toCurrency($value);
    }
}
