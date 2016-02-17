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
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Dojo_Form_Element_NumberTextBox */
#require_once 'Zend/Dojo/Form/Element/NumberTextBox.php';

/**
 * CurrencyTextBox dijit
 *
 * @uses       Zend_Dojo_Form_Element_NumberTextBox
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Dojo_Form_Element_CurrencyTextBox extends Zend_Dojo_Form_Element_NumberTextBox
{
    /**
     * Use CurrencyTextBox dijit view helper
     * @var string
     */
    public $helper = 'CurrencyTextBox';

    /**
     * Set currency
     *
     * @param  string $currency
     * @return Zend_Dojo_Form_Element_CurrencyTextBox
     */
    public function setCurrency($currency)
    {
        $this->setDijitParam('currency', (string) $currency);
        return $this;
    }

    /**
     * Retrieve currency
     *
     * @return string|null
     */
    public function getCurrency()
    {
        return $this->getDijitParam('currency');
    }

    /**
     * Set currency symbol
     *
     * Casts to string, uppercases, and trims to three characters.
     *
     * @param  string $symbol
     * @return Zend_Dojo_Form_Element_CurrencyTextBox
     */
    public function setSymbol($symbol)
    {
        $symbol = strtoupper((string) $symbol);
        $length = strlen($symbol);
        if (3 > $length) {
            #require_once 'Zend/Form/Element/Exception.php';
            throw new Zend_Form_Element_Exception('Invalid symbol provided; please provide ISO 4217 alphabetic currency code');
        }
        if (3 < $length) {
            $symbol = substr($symbol, 0, 3);
        }

        $this->setConstraint('symbol', $symbol);
        return $this;
    }

    /**
     * Retrieve symbol
     *
     * @return string|null
     */
    public function getSymbol()
    {
        return $this->getConstraint('symbol');
    }

    /**
     * Set whether currency is fractional
     *
     * @param  bool $flag
     * @return Zend_Dojo_Form_Element_CurrencyTextBox
     */
    public function setFractional($flag)
    {
        $this->setConstraint('fractional', (bool) $flag);
        return $this;
    }

    /**
     * Get whether or not to present fractional values
     *
     * @return bool
     */
    public function getFractional()
    {
        return ('true' == $this->getConstraint('fractional'));
    }
}
