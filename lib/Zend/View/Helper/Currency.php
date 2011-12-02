<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Currency.php 20785 2010-01-31 09:43:03Z mikaelkael $
 */

/** Zend_View_Helper_Abstract.php */
#require_once 'Zend/View/Helper/Abstract.php';

/**
 * Currency view helper
 *
 * @category  Zend
 * @package   Zend_View
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_Currency extends Zend_View_Helper_Abstract
{
    /**
     * Currency object
     *
     * @var Zend_Currency
     */
    protected $_currency;

    /**
     * Constructor for manually handling
     *
     * @param  Zend_Currency $currency Instance of Zend_Currency
     * @return void
     */
    public function __construct($currency = null)
    {
        if ($currency === null) {
            #require_once 'Zend/Registry.php';
            if (Zend_Registry::isRegistered('Zend_Currency')) {
                $currency = Zend_Registry::get('Zend_Currency');
            }
        }

        $this->setCurrency($currency);
    }

    /**
     * Output a formatted currency
     *
     * @param  integer|float                    $value    Currency value to output
     * @param  string|Zend_Locale|Zend_Currency $currency OPTIONAL Currency to use for this call
     * @return string Formatted currency
     */
    public function currency($value = null, $currency = null)
    {
        if ($value === null) {
            return $this;
        }

        if (is_string($currency) || ($currency instanceof Zend_Locale)) {
            #require_once 'Zend/Locale.php';
            if (Zend_Locale::isLocale($currency)) {
                $currency = array('locale' => $currency);
            }
        }

        if (is_string($currency)) {
            $currency = array('currency' => $currency);
        }

        if (is_array($currency)) {
            return $this->_currency->toCurrency($value, $currency);
        }

        return $this->_currency->toCurrency($value);
    }

    /**
     * Sets a currency to use
     *
     * @param  Zend_Currency|String|Zend_Locale $currency Currency to use
     * @throws Zend_View_Exception When no or a false currency was set
     * @return Zend_View_Helper_Currency
     */
    public function setCurrency($currency = null)
    {
        if (!$currency instanceof Zend_Currency) {
            #require_once 'Zend/Currency.php';
            $currency = new Zend_Currency($currency);
        }
        $this->_currency = $currency;

        return $this;
    }

    /**
     * Retrieve currency object
     *
     * @return Zend_Currency|null
     */
    public function getCurrency()
    {
        return $this->_currency;
    }
}
