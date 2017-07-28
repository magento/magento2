<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Model\Product\Option\Type;

use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;

/**
 * Catalog product option default type
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class DefaultType extends \Magento\Framework\DataObject
{
    /**
     * Option Instance
     *
     * @var \Magento\Catalog\Model\Product\Option
     * @since 2.0.0
     */
    protected $_option;

    /**
     * Product Instance
     *
     * @var \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    protected $_product;

    /**
     * TODO: Fill in description
     *
     * @var array
     * @since 2.0.0
     */
    protected $_productOptions = [];

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_scopeConfig;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $_checkoutSession;

    /**
     * Construct
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($data);
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Option Instance setter
     *
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return $this
     * @since 2.0.0
     */
    public function setOption($option)
    {
        $this->_option = $option;
        return $this;
    }

    /**
     * Option Instance getter
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Catalog\Model\Product\Option
     * @since 2.0.0
     */
    public function getOption()
    {
        if ($this->_option instanceof \Magento\Catalog\Model\Product\Option) {
            return $this->_option;
        }
        throw new LocalizedException(__('The option instance type in options group is incorrect.'));
    }

    /**
     * Product Instance setter
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     * @since 2.0.0
     */
    public function setProduct($product)
    {
        $this->_product = $product;
        return $this;
    }

    /**
     * Product Instance getter
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    public function getProduct()
    {
        if ($this->_product instanceof \Magento\Catalog\Model\Product) {
            return $this->_product;
        }
        throw new LocalizedException(__('The product instance type in options group is incorrect.'));
    }

    /**
     * Getter for Configuration Item Option
     *
     * @return \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface
     * @throws LocalizedException
     * @since 2.0.0
     */
    public function getConfigurationItemOption()
    {
        if ($this->_getData(
            'configuration_item_option'
        ) instanceof \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface
        ) {
            return $this->_getData('configuration_item_option');
        }

        // Back compatibility with quote specific keys to set configuration item options
        if ($this->_getData('quote_item_option') instanceof \Magento\Quote\Model\Quote\Item\Option) {
            return $this->_getData('quote_item_option');
        }

        throw new LocalizedException(__('The configuration item option instance in options group is incorrect.'));
    }

    /**
     * Getter for Configuration Item
     *
     * @return \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getConfigurationItem()
    {
        if ($this->_getData(
            'configuration_item'
        ) instanceof \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface
        ) {
            return $this->_getData('configuration_item');
        }

        // Back compatibility with quote specific keys to set configuration item
        if ($this->_getData('quote_item') instanceof \Magento\Quote\Model\Quote\Item) {
            return $this->_getData('quote_item');
        }

        throw new LocalizedException(__('The configuration item instance in options group is incorrect.'));
    }

    /**
     * Getter for Buy Request
     *
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getRequest()
    {
        if ($this->_getData('request') instanceof \Magento\Framework\DataObject) {
            return $this->_getData('request');
        }
        throw new LocalizedException(__('The BuyRequest instance in options group is incorrect.'));
    }

    /**
     * Store Config value
     *
     * @param string $key Config value key
     * @return string
     * @since 2.0.0
     */
    public function getConfigData($key)
    {
        return $this->_scopeConfig->getValue('catalog/custom_options/' . $key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Validate user input for option
     *
     * @param array $values All product option values, i.e. array (option_id => mixed, option_id => mixed...)
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function validateUserValue($values)
    {
        $this->_checkoutSession->setUseNotice(false);

        $this->setIsValid(false);

        $option = $this->getOption();
        if (!isset($values[$option->getId()]) && $option->getIsRequire() && !$this->getSkipCheckRequiredOption()) {
            throw new LocalizedException(__('Please specify product\'s required option(s).'));
        } elseif (isset($values[$option->getId()])) {
            $this->setUserValue($values[$option->getId()]);
            $this->setIsValid(true);
        }
        return $this;
    }

    /**
     * Check skip required option validation
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getSkipCheckRequiredOption()
    {
        return $this->getProduct()->getSkipCheckRequiredOption() ||
            $this->getProcessMode() == \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_LITE;
    }

    /**
     * Prepare option value for cart
     *
     * @return string|null Prepared option value
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function prepareForCart()
    {
        if ($this->getIsValid()) {
            return $this->getUserValue();
        }
        throw new LocalizedException(
            __('We can\'t add the product to the cart because of an option validation issue.')
        );
    }

    /**
     * Flag to indicate that custom option has own customized output (blocks, native html etc.)
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isCustomizedView()
    {
        return false;
    }

    /**
     * Return formatted option value for quote option
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     * @since 2.0.0
     */
    public function getFormattedOptionValue($optionValue)
    {
        return $optionValue;
    }

    /**
     * Return option html
     *
     * @param array $optionInfo
     * @return string
     * @since 2.0.0
     */
    public function getCustomizedView($optionInfo)
    {
        return isset($optionInfo['value']) ? $optionInfo['value'] : $optionInfo;
    }

    /**
     * Return printable option value
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     * @since 2.0.0
     */
    public function getPrintableOptionValue($optionValue)
    {
        return $optionValue;
    }

    /**
     * Return formatted option value ready to edit, ready to parse
     * (ex: Admin re-order, see \Magento\Sales\Model\AdminOrder\Create)
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     * @since 2.0.0
     */
    public function getEditableOptionValue($optionValue)
    {
        return $optionValue;
    }

    /**
     * Parse user input value and return cart prepared value, i.e. "one, two" => "1,2"
     *
     * @param string $optionValue
     * @param array $productOptionValues Values for product option
     * @return string|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function parseOptionValue($optionValue, $productOptionValues)
    {
        return $optionValue;
    }

    /**
     * Prepare option value for info buy request
     *
     * @param string $optionValue
     * @return string|null
     * @since 2.0.0
     */
    public function prepareOptionValueForRequest($optionValue)
    {
        return $optionValue;
    }

    /**
     * Return Price for selected option
     *
     * @param string $optionValue Prepared for cart option value
     * @param float $basePrice For percent price type
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function getOptionPrice($optionValue, $basePrice)
    {
        $option = $this->getOption();

        return $this->_getChargableOptionPrice($option->getPrice(), $option->getPriceType() == 'percent', $basePrice);
    }

    /**
     * Return SKU for selected option
     *
     * @param string $optionValue Prepared for cart option value
     * @param string $skuDelimiter Delimiter for Sku parts
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function getOptionSku($optionValue, $skuDelimiter)
    {
        return $this->getOption()->getSku();
    }

    /**
     * Return value => key all product options (using for parsing)
     *
     * @return array Array of Product custom options, reversing option values and option ids
     * @since 2.0.0
     */
    public function getProductOptions()
    {
        if (!isset($this->_productOptions[$this->getProduct()->getId()])) {
            $options = $this->getProduct()->getOptions();
            if ($options != null) {
                foreach ($options as $_option) {
                    /* @var $option \Magento\Catalog\Model\Product\Option */
                    $this->_productOptions[$this->getProduct()->getId()][$_option->getTitle()] = [
                        'option_id' => $_option->getId(),
                    ];
                    if ($_option->getGroupByType() == ProductCustomOptionInterface::OPTION_GROUP_SELECT) {
                        $optionValues = [];
                        foreach ($_option->getValues() as $_value) {
                            /* @var $value \Magento\Catalog\Model\Product\Option\Value */
                            $optionValues[$_value->getTitle()] = $_value->getId();
                        }
                        $this->_productOptions[$this
                            ->getProduct()
                            ->getId()][$_option
                            ->getTitle()]['values'] = $optionValues;
                    } else {
                        $this->_productOptions[$this->getProduct()->getId()][$_option->getTitle()]['values'] = [];
                    }
                }
            }
        }
        if (isset($this->_productOptions[$this->getProduct()->getId()])) {
            return $this->_productOptions[$this->getProduct()->getId()];
        }
        return [];
    }

    /**
     * Return final chargable price for option
     *
     * @param float $price Price of option
     * @param boolean $isPercent Price type - percent or fixed
     * @param float $basePrice For percent price type
     * @return float
     * @since 2.0.0
     */
    protected function _getChargableOptionPrice($price, $isPercent, $basePrice)
    {
        if ($isPercent) {
            return $basePrice * $price / 100;
        } else {
            return $price;
        }
    }
}
