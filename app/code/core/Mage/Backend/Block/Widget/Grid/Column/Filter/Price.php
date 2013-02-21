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
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Range grid column filter
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_Backend_Block_Widget_Grid_Column_Filter_Price extends Mage_Backend_Block_Widget_Grid_Column_Filter_Abstract
{
    /**
     * @var array
     */
    protected $_currencyList = null;

    /**
     * @var Mage_Directory_Model_Currency
     */
    protected $_currencyModel = null;

    /**
     * @var Mage_Directory_Model_Currency_DefaultLocator
     */
    protected $_currencyLocator = null;

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Model_Layout $layout
     * @param Mage_Core_Model_Event_Manager $eventManager
     * @param Mage_Backend_Model_Url $urlBuilder
     * @param Mage_Core_Model_Translate $translator
     * @param Mage_Core_Model_Cache $cache
     * @param Mage_Core_Model_Design_Package $designPackage
     * @param Mage_Core_Model_Session $session
     * @param Mage_Core_Model_Store_Config $storeConfig
     * @param Mage_Core_Controller_Varien_Front $frontController
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Mage_Core_Model_Dir $dirs
     * @param Mage_Core_Model_Logger $logger
     * @param Magento_Filesystem $filesystem
     * @param Mage_Directory_Model_Currency $currencyModel
     * @param Mage_Directory_Model_Currency_DefaultLocator $currencyLocator
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Mage_Core_Controller_Request_Http $request,
        Mage_Core_Model_Layout $layout,
        Mage_Core_Model_Event_Manager $eventManager,
        Mage_Backend_Model_Url $urlBuilder,
        Mage_Core_Model_Translate $translator,
        Mage_Core_Model_Cache $cache,
        Mage_Core_Model_Design_Package $designPackage,
        Mage_Core_Model_Session $session,
        Mage_Core_Model_Store_Config $storeConfig,
        Mage_Core_Controller_Varien_Front $frontController,
        Mage_Core_Model_Factory_Helper $helperFactory,
        Mage_Core_Model_Dir $dirs,
        Mage_Core_Model_Logger $logger,
        Magento_Filesystem $filesystem,
        Mage_Directory_Model_Currency $currencyModel,
        Mage_Directory_Model_Currency_DefaultLocator $currencyLocator,
        array $data = array()
    ) {
        parent::__construct($request, $layout, $eventManager, $urlBuilder, $translator, $cache, $designPackage,
            $session, $storeConfig, $frontController, $helperFactory, $dirs, $logger, $filesystem, $data
        );

        $this->_currencyModel = $currencyModel;
        $this->_currencyLocator = $currencyLocator;
    }

    /**
     * Retrieve html
     *
     * @return string
     */
    public function getHtml()
    {
        $html  = '<div class="range">';
        $html .= '<div class="range-line"><span class="label">'
            . Mage::helper('Mage_Backend_Helper_Data')->__('From')
            . ':</span> <input type="text" name="'
            . $this->_getHtmlName()
            . '[from]" id="' . $this->_getHtmlId() . '_from" value="'
            . $this->getEscapedValue('from') . '" class="input-text no-changes"  '
            . $this->getUiId('filter', $this->_getHtmlName(), 'from') . '/></div>';
        $html .= '<div class="range-line"><span class="label">'
            . Mage::helper('Mage_Backend_Helper_Data')->__('To')
            . ' : </span><input type="text" name="'
            . $this->_getHtmlName() . '[to]" id="' . $this->_getHtmlId() . '_to" value="'.$this->getEscapedValue('to')
            . '" class="input-text no-changes" ' . $this->getUiId('filter', $this->_getHtmlName(), 'to') . '/></div>';

        if ($this->getDisplayCurrencySelect()) {
            $html .= '<div class="range-line"><span class="label">'
                . Mage::helper('Mage_Backend_Helper_Data')->__('In') . ' : </span>'
                . $this->_getCurrencySelectHtml() . '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Retrieve display currency select
     *
     * @return bool|mixed
     */
    public function getDisplayCurrencySelect()
    {
        if (!is_null($this->getColumn()->getData('display_currency_select'))) {
            return $this->getColumn()->getData('display_currency_select');
        } else {
            return true;
        }
    }

    /**
     * Retrieve currency affect
     *
     * @return bool|mixed
     */
    public function getCurrencyAffect()
    {
        if (!is_null($this->getColumn()->getData('currency_affect'))) {
            return $this->getColumn()->getData('currency_affect');
        } else {
            return true;
        }
    }

    /**
     * Retrieve currency select html
     *
     * @return string
     */
    protected function _getCurrencySelectHtml()
    {
        $value = $this->getEscapedValue('currency');
        if (!$value) {
            $value = $this->_getColumnCurrencyCode();
        }

        $html  = '';
        $html .= '<select name="'.$this->_getHtmlName().'[currency]" id="'.$this->_getHtmlId().'_currency">';
        foreach ($this->_getCurrencyList() as $currency) {
            $html .= '<option value="' . $currency . '" '
                . ($currency == $value ? 'selected="selected"' : '').'>' . $currency . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Retrieve list of currencies
     *
     * @return array|null
     */
    protected function _getCurrencyList()
    {
        if (is_null($this->_currencyList)) {
            $this->_currencyList = $this->_currencyModel->getConfigAllowCurrencies();
        }
        return $this->_currencyList;
    }

    /**
     * Retrieve filter value
     *
     * @param null $index
     * @return mixed|null
     */
    public function getValue($index=null)
    {
        if ($index) {
            return $this->getData('value', $index);
        }
        $value = $this->getData('value');
        if ((isset($value['from']) && strlen($value['from']) > 0)
            || (isset($value['to']) && strlen($value['to']) > 0)
        ) {
            return $value;
        }
        return null;
    }

    /**
     * Retrieve filter condition
     *
     * @return array|mixed|null
     */
    public function getCondition()
    {
        $value = $this->getValue();

        if (isset($value['currency']) && $this->getCurrencyAffect()) {
            $displayCurrency = $value['currency'];
        } else {
            $displayCurrency = $this->_getColumnCurrencyCode();
        }
        $rate = $this->_getRate($displayCurrency, $this->_getColumnCurrencyCode());

        if (isset($value['from'])) {
            $value['from'] *= $rate;
        }

        if (isset($value['to'])) {
            $value['to'] *= $rate;
        }

        $this->prepareRates($displayCurrency);
        return $value;
    }

    /**
     * Retrieve column currency code
     *
     * @return string
     */
    protected function _getColumnCurrencyCode()
    {
        return $this->getColumn()->getCurrencyCode()?
            $this->getColumn()->getCurrencyCode() : $this->_currencyLocator->getDefaultCurrency($this->_request);
    }

    /**
     * Get currency rate
     *
     * @param $fromRate
     * @param $toRate
     * @return float
     */
    protected function _getRate($fromRate, $toRate)
    {
        return $this->_currencyModel->load($fromRate)->getAnyRate($toRate);
    }

    /**
     * Prepare currency rates
     *
     * @param $displayCurrency
     */
    public function prepareRates($displayCurrency)
    {
        $storeCurrency = $this->_getColumnCurrencyCode();

        $rate = $this->_getRate($storeCurrency, $displayCurrency);
        if ($rate) {
            $this->getColumn()->setRate($rate);
            $this->getColumn()->setCurrencyCode($displayCurrency);
        }
    }
}
