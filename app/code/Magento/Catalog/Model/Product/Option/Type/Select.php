<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Type;

use Magento\Catalog\Model\Product\Option\Value;
use Magento\Catalog\Pricing\Price\CalculateCustomOptionCatalogRule;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Product\Option;

/**
 * Catalog product option select type
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Select extends \Magento\Catalog\Model\Product\Option\Type\DefaultType
{
    /**
     * @var string|array
     */
    protected $_formattedOptionValue;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var array
     */
    private $singleSelectionTypes;

    /**
     * @var CalculateCustomOptionCatalogRule
     */
    private $calculateCustomOptionCatalogRule;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Escaper $escaper
     * @param array $data
     * @param array $singleSelectionTypes
     * @param CalculateCustomOptionCatalogRule|null $calculateCustomOptionCatalogRule
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Escaper $escaper,
        array $data = [],
        array $singleSelectionTypes = [],
        CalculateCustomOptionCatalogRule $calculateCustomOptionCatalogRule = null
    ) {
        $this->string = $string;
        $this->_escaper = $escaper;
        parent::__construct($checkoutSession, $scopeConfig, $data);

        $this->singleSelectionTypes = $singleSelectionTypes ?: [
            'drop_down' => \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
            'radio' => \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_RADIO,
        ];
        $this->calculateCustomOptionCatalogRule = $calculateCustomOptionCatalogRule ?? ObjectManager::getInstance()
                ->get(CalculateCustomOptionCatalogRule::class);
    }

    /**
     * Validate user input for option
     *
     * @param array $values All product option values, i.e. array (option_id => mixed, option_id => mixed...)
     * @return $this
     * @throws LocalizedException
     */
    public function validateUserValue($values)
    {
        parent::validateUserValue($values);

        $option = $this->getOption();
        $value = $this->getUserValue();

        if (empty($value) && $option->getIsRequire() && !$this->getSkipCheckRequiredOption()) {
            $this->setIsValid(false);
            throw new LocalizedException(
                __("The product's required option(s) weren't entered. Make sure the options are entered and try again.")
            );
        }
        if (!$this->_isSingleSelection()) {
            if (is_string($value)) {
                $value = explode(',', $value);
            }
            $valuesCollection = $option->getOptionValuesByOptionId($value, $this->getProduct()->getStoreId())->load();
            $valueCount = is_array($value) ? count($value) : 0;
            if ($valuesCollection->count() != $valueCount) {
                $this->setIsValid(false);
                throw new LocalizedException(
                    __(
                        "The product's required option(s) weren't entered. "
                        . "Make sure the options are entered and try again."
                    )
                );
            }
        }
        return $this;
    }

    /**
     * Prepare option value for cart
     *
     * @return string|null Prepared option value
     */
    public function prepareForCart()
    {
        if ($this->getIsValid() && $this->getUserValue()) {
            return is_array($this->getUserValue()) ? implode(',', $this->getUserValue()) : $this->getUserValue();
        } else {
            return null;
        }
    }

    /**
     * Return formatted option value for quote option
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     */
    public function getFormattedOptionValue($optionValue)
    {
        if ($this->_formattedOptionValue === null) {
            $this->_formattedOptionValue = $this->_escaper->escapeHtml($this->getEditableOptionValue($optionValue));
        }
        return $this->_formattedOptionValue;
    }

    /**
     * Return printable option value
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     */
    public function getPrintableOptionValue($optionValue)
    {
        return $this->getFormattedOptionValue($optionValue);
    }

    /**
     * Return currently unavailable product configuration message
     *
     * @return \Magento\Framework\Phrase
     */
    protected function _getWrongConfigurationMessage()
    {
        return __('Some of the selected item options are not currently available.');
    }

    /**
     * Return formatted option value ready to edit, ready to parse
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     */
    public function getEditableOptionValue($optionValue)
    {
        $option = $this->getOption();
        $result = '';
        if (!$this->_isSingleSelection()) {
            foreach (explode(',', (string)$optionValue) as $_value) {
                $_result = $option->getValueById($_value);
                if ($_result) {
                    $result .= $_result->getTitle() . ', ';
                } else {
                    if ($this->getListener()) {
                        $this->getListener()->setHasError(true)->setMessage($this->_getWrongConfigurationMessage());
                        $result = '';
                        break;
                    }
                }
            }
            $result = $this->string->substr($result, 0, -2);
        } elseif ($this->_isSingleSelection()) {
            $_result = $option->getValueById($optionValue);
            if ($_result) {
                $result = $_result->getTitle();
            } else {
                if ($this->getListener()) {
                    $this->getListener()->setHasError(true)->setMessage($this->_getWrongConfigurationMessage());
                }
                $result = '';
            }
        } else {
            $result = $optionValue;
        }
        return $result;
    }

    /**
     * Parse user input value and return cart prepared value, i.e. "one, two" => "1,2"
     *
     * @param string $optionValue
     * @param array $productOptionValues Values for product option
     * @return string|null
     */
    public function parseOptionValue($optionValue, $productOptionValues)
    {
        $values = [];
        if (!$this->_isSingleSelection()) {
            foreach (explode(',', $optionValue) as $value) {
                $value = $value === null ? '' : trim($value);
                if (array_key_exists($value, $productOptionValues)) {
                    $values[] = $productOptionValues[$value];
                }
            }
        } elseif ($this->_isSingleSelection() && array_key_exists($optionValue, $productOptionValues)) {
            $values[] = $productOptionValues[$optionValue];
        }
        if (count($values)) {
            return implode(',', $values);
        } else {
            return null;
        }
    }

    /**
     * Prepare option value for info buy request
     *
     * @param string $optionValue
     * @return string
     */
    public function prepareOptionValueForRequest($optionValue)
    {
        if (!$this->_isSingleSelection()) {
            return explode(',', (string)$optionValue);
        }
        return $optionValue;
    }

    /**
     * Return Price for selected option
     *
     * @param string $optionValue Prepared for cart option value
     * @param float $basePrice
     * @return float
     */
    public function getOptionPrice($optionValue, $basePrice)
    {
        $option = $this->getOption();
        $result = 0;

        if (!$this->_isSingleSelection()) {
            foreach (explode(',', (string)$optionValue) as $value) {
                $_result = $option->getValueById($value);
                if ($_result) {
                    $result += $this->getCalculatedOptionValue($option, $_result, $basePrice);
                } else {
                    if ($this->getListener()) {
                        $this->getListener()->setHasError(true)->setMessage($this->_getWrongConfigurationMessage());
                        break;
                    }
                }
            }
        } elseif ($this->_isSingleSelection()) {
            $_result = $option->getValueById($optionValue);
            if ($_result) {
                $catalogPriceValue = $this->calculateCustomOptionCatalogRule->execute(
                    $option->getProduct(),
                    (float)$_result->getPrice(),
                    $_result->getPriceType() === Value::TYPE_PERCENT
                );
                if ($catalogPriceValue !== null) {
                    $result = $catalogPriceValue;
                } else {
                    $result = $this->_getChargeableOptionPrice(
                        $_result->getPrice(),
                        $_result->getPriceType() == 'percent',
                        $basePrice
                    );
                }
            } else {
                if ($this->getListener()) {
                    $this->getListener()->setHasError(true)->setMessage($this->_getWrongConfigurationMessage());
                }
            }
        }

        return $result;
    }

    /**
     * Return SKU for selected option
     *
     * @param string $optionValue Prepared for cart option value
     * @param string $skuDelimiter Delimiter for Sku parts
     * @return string
     */
    public function getOptionSku($optionValue, $skuDelimiter)
    {
        $option = $this->getOption();

        if (!$this->_isSingleSelection()) {
            $skus = [];
            foreach (explode(',', (string)$optionValue) as $value) {
                $optionSku = $option->getValueById($value);
                if ($optionSku) {
                    $skus[] = $optionSku->getSku();
                } else {
                    if ($this->getListener()) {
                        $this->getListener()->setHasError(true)->setMessage($this->_getWrongConfigurationMessage());
                        break;
                    }
                }
            }
            $result = implode($skuDelimiter, $skus);
        } elseif ($this->_isSingleSelection()) {
            $result = $option->getValueById($optionValue);
            if ($result) {
                return $result->getSku();
            } else {
                if ($this->getListener()) {
                    $this->getListener()->setHasError(true)->setMessage($this->_getWrongConfigurationMessage());
                }
                return '';
            }
        } else {
            $result = parent::getOptionSku($optionValue, $skuDelimiter);
        }

        return $result;
    }

    /**
     * Check if option has single or multiple values selection
     *
     * @return boolean
     */
    protected function _isSingleSelection()
    {
        return in_array($this->getOption()->getType(), $this->singleSelectionTypes, true);
    }

    /**
     * Returns calculated price of option
     *
     * @param Option $option
     * @param Option\Value $result
     * @param float $basePrice
     * @return float
     */
    protected function getCalculatedOptionValue(Option $option, Value $result, float $basePrice) : float
    {
        $catalogPriceValue = $this->calculateCustomOptionCatalogRule->execute(
            $option->getProduct(),
            (float)$result->getPrice(),
            $result->getPriceType() === Value::TYPE_PERCENT
        );
        if ($catalogPriceValue !== null) {
            $optionCalculatedValue = $catalogPriceValue;
        } else {
            $optionCalculatedValue = $this->_getChargeableOptionPrice(
                $result->getPrice(),
                $result->getPriceType() === Value::TYPE_PERCENT,
                $basePrice
            );
        }
        return $optionCalculatedValue;
    }
}
