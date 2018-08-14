<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

/**
 * Product form price field helper
 */
class Price extends \Magento\Framework\Data\Form\Element\Text
{
    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Tax\Helper\Data $taxData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Tax\Helper\Data $taxData,
        array $data = []
    ) {
        $this->_localeCurrency = $localeCurrency;
        $this->_storeManager = $storeManager;
        $this->_taxData = $taxData;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addClass('validate-zero-or-greater');
    }

    /**
     * @return mixed
     */
    public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();

        $addJsObserver = false;
        if ($attribute = $this->getEntityAttribute()) {
            $store = $this->getStore($attribute);
            if ($this->getType() !== 'hidden') {
                $html .= '<strong>'
                    . $this->_localeCurrency->getCurrency($store->getBaseCurrencyCode())->getSymbol()
                    . '</strong>';
            }
            if ($this->_taxData->priceIncludesTax($store)) {
                if ($attribute->getAttributeCode() !== 'cost') {
                    $addJsObserver = true;
                    $html .= ' <strong>[' . __(
                        'Inc. Tax'
                    ) . '<span id="dynamic-tax-' . $attribute->getAttributeCode() . '"></span>]</strong>';
                }
            }
        }
        if ($addJsObserver) {
            $html .= $this->_getTaxObservingCode($attribute);
        }

        return $html;
    }

    /**
     * @param mixed $attribute
     * @return string
     */
    protected function _getTaxObservingCode($attribute)
    {
        $html = "<script type='text/javascript'>if (dynamicTaxes == undefined) var dynamicTaxes = new Array();"
            . " dynamicTaxes[dynamicTaxes.length]='{$attribute->getAttributeCode()}'</script>";
        return $html;
    }

    /**
     * @param mixed $attribute
     * @return \Magento\Store\Model\Store
     */
    protected function getStore($attribute)
    {
        if (!($storeId = $attribute->getStoreId())) {
            $storeId = $this->getForm()->getDataObject()->getStoreId();
        }
        $store = $this->_storeManager->getStore($storeId);
        return $store;
    }

    /**
     * @param null|int|string $index
     * @return null|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getEscapedValue($index = null)
    {
        $value = $this->getValue();

        if (!is_numeric($value)) {
            return null;
        }

        if ($attribute = $this->getEntityAttribute()) {
            // honor the currency format of the store
            $store = $this->getStore($attribute);
            $currency = $this->_localeCurrency->getCurrency($store->getBaseCurrencyCode());
            $value = $currency->toCurrency($value, ['display' => \Magento\Framework\Currency::NO_SYMBOL]);
        } else {
            // default format:  1234.56
            $value = number_format($value, 2, null, '');
        }

        return $value;
    }
}
