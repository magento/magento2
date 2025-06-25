<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model\Product\Option\Validator;

use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Validator\ValidateException;

/**
 * Product option default validator
 */
class DefaultValidator extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * @var string[]
     */
    protected $productOptionTypes;

    /**
     * @var string[]
     */
    protected $priceTypes;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    private $localeFormat;

    /**
     * @param \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig
     * @param \Magento\Catalog\Model\Config\Source\Product\Options\Price $priceConfig
     * @param \Magento\Framework\Locale\FormatInterface|null $localeFormat
     */
    public function __construct(
        \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig,
        \Magento\Catalog\Model\Config\Source\Product\Options\Price $priceConfig,
        ?\Magento\Framework\Locale\FormatInterface $localeFormat = null
    ) {
        foreach ($productOptionConfig->getAll() as $option) {
            foreach ($option['types'] as $type) {
                $this->productOptionTypes[] = $type['name'];
            }
        }

        foreach ($priceConfig->toOptionArray() as $item) {
            $this->priceTypes[] = $item['value'];
        }

        $this->localeFormat = $localeFormat ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Locale\FormatInterface::class);
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  \Magento\Catalog\Model\Product\Option $value
     * @return boolean
     * @throws ValidateException If validation of $value is impossible
     */
    public function isValid($value)
    {
        $messages = [];

        if (!$this->validateOptionRequiredFields($value)) {
            $messages['option required fields'] = 'Missed values for option required fields';
        }

        if (!$this->validateOptionType($value)) {
            $messages['option type'] = 'Invalid option type';
        }

        if (!$this->validateOptionValue($value)) {
            $messages['option values'] = 'Invalid option value';
        }

        $this->_addMessages($messages);

        return empty($messages);
    }

    /**
     * Validate option required fields
     *
     * @param Option $option
     * @return bool
     */
    protected function validateOptionRequiredFields(Option $option)
    {
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        $product = $option->getProduct();
        if ($product) {
            $storeId = $product->getStoreId();
        }
        $title = $option->getTitle();
        return $this->isValidOptionTitle($title, $storeId) && !$this->isEmpty($option->getType());
    }

    /**
     * Validate option title
     *
     * @param string $title
     * @param int $storeId
     * @return bool
     */
    protected function isValidOptionTitle($title, $storeId)
    {
        // we should be able to set null title for not default store (used for deletion from store view)
        if ($storeId > \Magento\Store\Model\Store::DEFAULT_STORE_ID && $title === null) {
            return true;
        }

        // checking whether title is null and is empty string
        if ($title === null || $title === '') {
            return false;
        }

        return true;
    }

    /**
     * Validate option type fields
     *
     * @param Option $option
     * @return bool
     */
    protected function validateOptionType(Option $option)
    {
        return $this->isInRange($option->getType(), $this->productOptionTypes);
    }

    /**
     * Validate option type fields
     *
     * @param Option $option
     * @return bool
     */
    protected function validateOptionValue(Option $option)
    {
        return $this->isInRange($option->getPriceType(), $this->priceTypes) && $this->isNumber($option->getPrice());
    }

    /**
     * Check whether the value is empty
     *
     * @param mixed $value
     * @return bool
     */
    protected function isEmpty($value)
    {
        return empty($value);
    }

    /**
     * Check whether the value is in range
     *
     * @param string $value
     * @param array $range
     * @return bool
     */
    protected function isInRange($value, array $range)
    {
        return in_array($value, $range);
    }

    /**
     * Check whether the value is negative
     *
     * @param string $value
     * @return bool
     */
    protected function isNegative($value)
    {
        return $this->localeFormat->getNumber($value) < 0;
    }

    /**
     * Check whether the value is a number
     *
     * @param string $value
     * @return bool
     */
    public function isNumber($value)
    {
        return is_numeric($this->localeFormat->getNumber($value));
    }
}
