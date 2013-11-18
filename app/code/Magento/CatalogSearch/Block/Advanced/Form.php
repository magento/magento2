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
 * @category    Magento
 * @package     Magento_CatalogSearch
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Advanced search form
 *
 * @category   Magento
 * @package    Magento_CatalogSearch
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogSearch\Block\Advanced;

class Form extends \Magento\Core\Block\Template
{
    /**
     * Locale
     *
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Currency factory
     *
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * Catalog search advanced
     *
     * @var \Magento\CatalogSearch\Model\Advanced
     */
    protected $_catalogSearchAdvanced;

    /**
     * Construct
     *
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\CatalogSearch\Model\Advanced $catalogSearchAdvanced
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\CatalogSearch\Model\Advanced $catalogSearchAdvanced,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\LocaleInterface $locale,
        array $data = array()
    ) {
        $this->_catalogSearchAdvanced = $catalogSearchAdvanced;
        $this->_currencyFactory = $currencyFactory;
        $this->_storeManager = $storeManager;
        $this->_locale = $locale;
        parent::__construct($coreData, $context, $data);
    }

    public function _prepareLayout()
    {
        // add Home breadcrumb
        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', array(
                'label'=>__('Home'),
                'title'=>__('Go to Home Page'),
                'link' => $this->_storeManager->getStore()->getBaseUrl(),
            ))->addCrumb('search', array(
                'label'=>__('Catalog Advanced Search')
            ));
        }
        return parent::_prepareLayout();
    }

    /**
     * Retrieve collection of product searchable attributes
     *
     * @return \Magento\Data\Collection\Db
     */
    public function getSearchableAttributes()
    {
        $attributes = $this->getModel()->getAttributes();
        return $attributes;
    }

    /**
     * Retrieve attribute label
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return string
     */
    public function getAttributeLabel($attribute)
    {
        return $attribute->getStoreLabel();
    }

    /**
     * Retrieve attribute input validation class
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return string
     */
    public function getAttributeValidationClass($attribute)
    {
        return $attribute->getFrontendClass();
    }

    /**
     * Retrieve search string for given field from request
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param string|null $part
     * @return mixed|string
     */
    public function getAttributeValue($attribute, $part = null)
    {
        $value = $this->getRequest()->getQuery($attribute->getAttributeCode());
        if ($part && $value) {
            if (isset($value[$part])) {
                $value = $value[$part];
            } else {
                $value = '';
            }
        }

        return $value;
    }

    /**
     * Retrieve the list of available currencies
     *
     * @return array
     */
    public function getAvailableCurrencies()
    {
        $currencies = $this->getData('_currencies');
        if (is_null($currencies)) {
            $currencies = array();
            $codes = $this->_storeManager->getStore()->getAvailableCurrencyCodes(true);
            if (is_array($codes) && count($codes)) {
                $rates = $this->_currencyFactory->create()->getCurrencyRates(
                    $this->_storeManager->getStore()->getBaseCurrency(),
                    $codes
                );

                foreach ($codes as $code) {
                    if (isset($rates[$code])) {
                        $currencies[$code] = $code;
                    }
                }
            }

            $this->setData('currencies', $currencies);
        }
        return $currencies;
    }

    /**
     * Count available currencies
     *
     * @return int
     */
    public function getCurrencyCount()
    {
        return count($this->getAvailableCurrencies());
    }

    /**
     * Retrieve currency code for attribute
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return string
     */
    public function getCurrency($attribute)
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();

        $baseCurrency = $this->_storeManager->getStore()->getBaseCurrency()->getCurrencyCode();
        return $this->getAttributeValue($attribute, 'currency') ?
            $this->getAttributeValue($attribute, 'currency') : $baseCurrency;
    }

    /**
     * Retrieve attribute input type
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return  string
     */
    public function getAttributeInputType($attribute)
    {
        $dataType   = $attribute->getBackend()->getType();
        $imputType  = $attribute->getFrontend()->getInputType();
        if ($imputType == 'select' || $imputType == 'multiselect') {
            return 'select';
        }

        if ($imputType == 'boolean') {
            return 'yesno';
        }

        if ($imputType == 'price') {
            return 'price';
        }

        if ($dataType == 'int' || $dataType == 'decimal') {
            return 'number';
        }

        if ($dataType == 'datetime') {
            return 'date';
        }

        return 'string';
    }

    /**
     * Build attribute select element html string
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return string
     */
    public function getAttributeSelectElement($attribute)
    {
        $extra = '';
        $options = $attribute->getSource()->getAllOptions(false);

        $name = $attribute->getAttributeCode();

        // 2 - avoid yes/no selects to be multiselects
        if (is_array($options) && count($options)>2) {
            $extra = 'multiple="multiple" size="4"';
            $name.= '[]';
        }
        else {
            array_unshift($options, array('value'=>'', 'label'=>__('All')));
        }

        return $this->_getSelectBlock()
            ->setName($name)
            ->setId($attribute->getAttributeCode())
            ->setTitle($this->getAttributeLabel($attribute))
            ->setExtraParams($extra)
            ->setValue($this->getAttributeValue($attribute))
            ->setOptions($options)
            ->setClass('multiselect')
            ->getHtml();
    }

    /**
     * Retrieve yes/no element html for provided attribute
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return string
     */
    public function getAttributeYesNoElement($attribute)
    {
        $options = array(
            array('value' => '',  'label' => __('All')),
            array('value' => '1', 'label' => __('Yes')),
            array('value' => '0', 'label' => __('No'))
        );

        $name = $attribute->getAttributeCode();
        return $this->_getSelectBlock()
            ->setName($name)
            ->setId($attribute->getAttributeCode())
            ->setTitle($this->getAttributeLabel($attribute))
            ->setExtraParams("")
            ->setValue($this->getAttributeValue($attribute))
            ->setOptions($options)
            ->getHtml();
    }

    protected function _getSelectBlock()
    {
        $block = $this->getData('_select_block');
        if (is_null($block)) {
            $block = $this->getLayout()->createBlock('Magento\Core\Block\Html\Select');
            $this->setData('_select_block', $block);
        }
        return $block;
    }

    protected function _getDateBlock()
    {
        $block = $this->getData('_date_block');
        if (is_null($block)) {
            $block = $this->getLayout()->createBlock('Magento\Core\Block\Html\Date');
            $this->setData('_date_block', $block);
        }
        return $block;
    }

    /**
     * Retrieve advanced search model object
     *
     * @return \Magento\CatalogSearch\Model\Advanced
     */
    public function getModel()
    {
        return $this->_catalogSearchAdvanced;
    }

    /**
     * Retrieve search form action url
     *
     * @return string
     */
    public function getSearchPostUrl()
    {
        return $this->getUrl('*/*/result');
    }

    /**
     * Build date element html string for attribute
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param string $part
     * @return string
     */
    public function getDateInput($attribute, $part = 'from')
    {
        $name = $attribute->getAttributeCode() . '[' . $part . ']';
        $value = $this->getAttributeValue($attribute, $part);

        return $this->_getDateBlock()
            ->setName($name)
            ->setId($attribute->getAttributeCode() . ($part == 'from' ? '' : '_' . $part))
            ->setTitle($this->getAttributeLabel($attribute))
            ->setValue($value)
            ->setImage($this->getViewFileUrl('Magento_Core::calendar.gif'))
            ->setDateFormat($this->_locale->getDateFormat(\Magento\Core\Model\LocaleInterface::FORMAT_TYPE_SHORT))
            ->setClass('input-text')
            ->getHtml();
    }
}
