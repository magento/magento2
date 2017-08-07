<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Advanced search form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogSearch\Block\Advanced;

use Magento\CatalogSearch\Model\Advanced;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * @api
 */
class Form extends Template
{
    /**
     * Currency factory
     *
     * @var CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * Catalog search advanced
     *
     * @var Advanced
     */
    protected $_catalogSearchAdvanced;

    /**
     * @param Context $context
     * @param Advanced $catalogSearchAdvanced
     * @param CurrencyFactory $currencyFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Advanced $catalogSearchAdvanced,
        CurrencyFactory $currencyFactory,
        array $data = []
    ) {
        $this->_catalogSearchAdvanced = $catalogSearchAdvanced;
        $this->_currencyFactory = $currencyFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return AbstractBlock
     */
    public function _prepareLayout()
    {
        // add Home breadcrumb
        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            )->addCrumb(
                'search',
                ['label' => __('Catalog Advanced Search')]
            );
        }
        return parent::_prepareLayout();
    }

    /**
     * Retrieve collection of product searchable attributes
     *
     * @return DbCollection
     */
    public function getSearchableAttributes()
    {
        $attributes = $this->_catalogSearchAdvanced->getAttributes();
        return $attributes;
    }

    /**
     * Retrieve attribute label
     *
     * @param AbstractAttribute $attribute
     * @return string
     */
    public function getAttributeLabel($attribute)
    {
        return $attribute->getStoreLabel();
    }

    /**
     * Retrieve attribute input validation class
     *
     * @param AbstractAttribute $attribute
     * @return string
     */
    public function getAttributeValidationClass($attribute)
    {
        return $attribute->getFrontendClass();
    }

    /**
     * Retrieve search string for given field from request
     *
     * @param AbstractAttribute $attribute
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
        if ($currencies === null) {
            $currencies = [];
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
     * @param AbstractAttribute $attribute
     * @return string
     */
    public function getCurrency($attribute)
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();

        $baseCurrency = $this->_storeManager->getStore()->getBaseCurrency()->getCurrencyCode();
        return $this->getAttributeValue(
            $attribute,
            'currency'
        ) ? $this->getAttributeValue(
            $attribute,
            'currency'
        ) : $baseCurrency;
    }

    /**
     * Retrieve attribute input type
     *
     * @param AbstractAttribute $attribute
     * @return  string
     */
    public function getAttributeInputType($attribute)
    {
        $dataType = $attribute->getBackend()->getType();
        $imputType = $attribute->getFrontend()->getInputType();
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
     * @param AbstractAttribute $attribute
     * @return string
     */
    public function getAttributeSelectElement($attribute)
    {
        $extra = '';
        $options = $attribute->getSource()->getAllOptions(false);

        $name = $attribute->getAttributeCode();

        // 2 - avoid yes/no selects to be multiselects
        if (is_array($options) && count($options) > 2) {
            $extra = 'multiple="multiple" size="4"';
            $name .= '[]';
        } else {
            array_unshift($options, ['value' => '', 'label' => __('All')]);
        }

        return $this->_getSelectBlock()->setName(
            $name
        )->setId(
            $attribute->getAttributeCode()
        )->setTitle(
            $this->getAttributeLabel($attribute)
        )->setExtraParams(
            $extra
        )->setValue(
            $this->getAttributeValue($attribute)
        )->setOptions(
            $options
        )->setClass(
            'multiselect'
        )->getHtml();
    }

    /**
     * Retrieve yes/no element html for provided attribute
     *
     * @param AbstractAttribute $attribute
     * @return string
     */
    public function getAttributeYesNoElement($attribute)
    {
        $options = [
            ['value' => '', 'label' => __('All')],
            ['value' => '1', 'label' => __('Yes')],
            ['value' => '0', 'label' => __('No')],
        ];

        $name = $attribute->getAttributeCode();
        return $this->_getSelectBlock()->setName(
            $name
        )->setId(
            $attribute->getAttributeCode()
        )->setTitle(
            $this->getAttributeLabel($attribute)
        )->setExtraParams(
            ""
        )->setValue(
            $this->getAttributeValue($attribute)
        )->setOptions(
            $options
        )->getHtml();
    }

    /**
     * @return BlockInterface
     */
    protected function _getSelectBlock()
    {
        $block = $this->getData('_select_block');
        if ($block === null) {
            $block = $this->getLayout()->createBlock(\Magento\Framework\View\Element\Html\Select::class);
            $this->setData('_select_block', $block);
        }
        return $block;
    }

    /**
     * @return BlockInterface|mixed
     */
    protected function _getDateBlock()
    {
        $block = $this->getData('_date_block');
        if ($block === null) {
            $block = $this->getLayout()->createBlock(\Magento\Framework\View\Element\Html\Date::class);
            $this->setData('_date_block', $block);
        }
        return $block;
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
     * @param AbstractAttribute $attribute
     * @param string $part
     * @return string
     */
    public function getDateInput($attribute, $part = 'from')
    {
        $name = $attribute->getAttributeCode() . '[' . $part . ']';
        $value = $this->getAttributeValue($attribute, $part);

        return $this->_getDateBlock()->setName(
            $name
        )->setId(
            $attribute->getAttributeCode() . ($part == 'from' ? '' : '_' . $part)
        )->setTitle(
            $this->getAttributeLabel($attribute)
        )->setValue(
            $value
        )->setImage(
            $this->getViewFileUrl('Magento_Theme::calendar.png')
        )->setDateFormat(
            $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT)
        )->setClass(
            'input-text'
        )->getHtml();
    }
}
