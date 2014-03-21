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
 * @package     Magento_Bundle
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Bundle\Block\Catalog\Product\View\Type\Bundle;

/**
 * Bundle option renderer
 *
 * @category    Magento
 * @package     Magento_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Option extends \Magento\Bundle\Block\Catalog\Product\Price
{
    /**
     * Store preconfigured options
     *
     * @var int|array|string
     */
    protected $_selectedOptions = null;

    /**
     * Show if option has a single selection
     *
     * @var bool
     */
    protected $_showSingle = null;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreHelper;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Registry $registry
     * @param \Magento\Stdlib\String $string
     * @param \Magento\Math\Random $mathRandom
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Tax\Model\Calculation $taxCalc
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Registry $registry,
        \Magento\Stdlib\String $string,
        \Magento\Math\Random $mathRandom,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Tax\Model\Calculation $taxCalc,
        \Magento\Core\Helper\Data $coreHelper,
        array $data = array()
    ) {
        $this->_coreHelper = $coreHelper;
        parent::__construct(
            $context,
            $jsonEncoder,
            $catalogData,
            $taxData,
            $registry,
            $string,
            $mathRandom,
            $cartHelper,
            $taxCalc,
            $data
        );
    }

    /**
     * Check if option has a single selection
     *
     * @return bool
     */
    public function showSingle()
    {
        if (is_null($this->_showSingle)) {
            $_option = $this->getOption();
            $_selections = $_option->getSelections();

            $this->_showSingle = count($_selections) == 1 && $_option->getRequired();
        }

        return $this->_showSingle;
    }

    /**
     * Retrieve default values for template
     *
     * @return array
     */
    public function getDefaultValues()
    {
        $_option = $this->getOption();
        $_default = $_option->getDefaultSelection();
        $_selections = $_option->getSelections();
        $selectedOptions = $this->_getSelectedOptions();
        $inPreConfigured = $this->getProduct()->hasPreconfiguredValues() &&
            $this->getProduct()->getPreconfiguredValues()->getData('bundle_option_qty/' . $_option->getId());

        if (empty($selectedOptions) && $_default) {
            $_defaultQty = $_default->getSelectionQty() * 1;
            $_canChangeQty = $_default->getSelectionCanChangeQty();
        } elseif (!$inPreConfigured && $selectedOptions && is_numeric($selectedOptions)) {
            $selectedSelection = $_option->getSelectionById($selectedOptions);
            $_defaultQty = $selectedSelection->getSelectionQty() * 1;
            $_canChangeQty = $selectedSelection->getSelectionCanChangeQty();
        } elseif (!$this->showSingle() || $inPreConfigured) {
            $_defaultQty = $this->_getSelectedQty();
            $_canChangeQty = (bool)$_defaultQty;
        } else {
            $_defaultQty = $_selections[0]->getSelectionQty() * 1;
            $_canChangeQty = $_selections[0]->getSelectionCanChangeQty();
        }

        return array($_defaultQty, $_canChangeQty);
    }

    /**
     * Collect selected options
     *
     * @return int|array|string
     */
    protected function _getSelectedOptions()
    {
        if (is_null($this->_selectedOptions)) {
            $this->_selectedOptions = array();
            $option = $this->getOption();

            if ($this->getProduct()->hasPreconfiguredValues()) {
                $configValue = $this->getProduct()->getPreconfiguredValues()->getData(
                    'bundle_option/' . $option->getId()
                );
                if ($configValue) {
                    $this->_selectedOptions = $configValue;
                } elseif (!$option->getRequired()) {
                    $this->_selectedOptions = 'None';
                }
            }
        }

        return $this->_selectedOptions;
    }

    /**
     * Define if selection is selected
     *
     * @param  \Magento\Catalog\Model\Product $selection
     * @return bool
     */
    public function isSelected($selection)
    {
        $selectedOptions = $this->_getSelectedOptions();
        if (is_numeric($selectedOptions)) {
            return $selection->getSelectionId() == $selectedOptions;
        } elseif (is_array($selectedOptions) && !empty($selectedOptions)) {
            return in_array($selection->getSelectionId(), $selectedOptions);
        } elseif ($selectedOptions == 'None') {
            return false;
        } else {
            return $selection->getIsDefault() && $selection->isSaleable();
        }
    }

    /**
     * Retrieve selected option qty
     *
     * @return int
     */
    protected function _getSelectedQty()
    {
        if ($this->getProduct()->hasPreconfiguredValues()) {
            $selectedQty = (double)$this->getProduct()->getPreconfiguredValues()->getData(
                'bundle_option_qty/' . $this->getOption()->getId()
            );
            if ($selectedQty < 0) {
                $selectedQty = 0;
            }
        } else {
            $selectedQty = 0;
        }

        return $selectedQty;
    }

    /**
     * Get product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->_coreRegistry->registry('current_product'));
        }
        return $this->getData('product');
    }

    /**
     * @param mixed $_selection
     * @param bool $includeContainer
     * @return string
     */
    public function getSelectionQtyTitlePrice($_selection, $includeContainer = true)
    {
        $price = $this->getProduct()->getPriceModel()->getSelectionPreFinalPrice($this->getProduct(), $_selection);
        $this->setFormatProduct($_selection);
        $priceTitle = $_selection->getSelectionQty() * 1 . ' x ' . $this->escapeHtml($_selection->getName());

        $priceTitle .= ' &nbsp; ' . ($includeContainer ? '<span class="price-notice">' : '') . '+' .
            $this->formatPriceString($price, $includeContainer) . ($includeContainer ? '</span>' : '');

        return $priceTitle;
    }

    /**
     * Get price for selection product
     *
     * @param \Magento\Catalog\Model\Product $_selection
     * @return int|float
     */
    public function getSelectionPrice($_selection)
    {
        $price = 0;
        $store = $this->getProduct()->getStore();
        if ($_selection) {
            $price = $this->getProduct()->getPriceModel()->getSelectionPreFinalPrice(
                $this->getProduct(),
                $_selection,
                1
            );
            if (is_numeric($price)) {
                $price = $this->_coreHelper->currencyByStore($price, $store, false);
            }
        }
        return is_numeric($price) ? $price : 0;
    }

    /**
     * Get title price for selection product
     *
     * @param \Magento\Catalog\Model\Product $_selection
     * @param bool $includeContainer
     * @return string
     */
    public function getSelectionTitlePrice($_selection, $includeContainer = true)
    {
        $price = $this->getProduct()->getPriceModel()->getSelectionPreFinalPrice($this->getProduct(), $_selection, 1);
        $this->setFormatProduct($_selection);
        $priceTitle = $this->escapeHtml($_selection->getName());
        $priceTitle .= ' &nbsp; ' . ($includeContainer ? '<span class="price-notice">' : '') . '+' .
            $this->formatPriceString($price, $includeContainer) . ($includeContainer ? '</span>' : '');
        return $priceTitle;
    }

    /**
     * Set JS validation container for element
     *
     * @param int $elementId
     * @param int $containerId
     * @return string
     */
    public function setValidationContainer($elementId, $containerId)
    {
        return;
    }

    /**
     * Format price string
     *
     * @param float $price
     * @param bool $includeContainer
     * @return string
     */
    public function formatPriceString($price, $includeContainer = true)
    {
        $taxHelper = $this->_taxData;
        $coreHelper = $this->_coreHelper;
        $currentProduct = $this->getProduct();
        if ($currentProduct->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC &&
            $this->getFormatProduct()
        ) {
            $product = $this->getFormatProduct();
        } else {
            $product = $currentProduct;
        }

        $priceTax = $taxHelper->getPrice($product, $price);
        $priceIncTax = $taxHelper->getPrice($product, $price, true);

        $formated = $coreHelper->currencyByStore($priceTax, $product->getStore(), true, $includeContainer);
        if ($taxHelper->displayBothPrices() && $priceTax != $priceIncTax) {
            $formated .= ' (+' . $coreHelper->currencyByStore(
                $priceIncTax,
                $product->getStore(),
                true,
                $includeContainer
            ) . ' ' . __(
                'Incl. Tax'
            ) . ')';
        }

        return $formated;
    }

    /**
     * Clear selected option when setting new option
     *
     * @param \Magento\Bundle\Model\Option $option
     * @return mixed
     */
    public function setOption(\Magento\Bundle\Model\Option $option)
    {
        $this->_selectedOptions = null;
        return parent::setOption($option);
    }
}
