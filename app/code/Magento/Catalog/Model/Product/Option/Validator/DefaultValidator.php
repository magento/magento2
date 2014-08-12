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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Model\Product\Option\Validator;

use Zend_Validate_Exception;
use Magento\Catalog\Model\Product\Option;

class DefaultValidator extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * Product option types
     *
     * @var string[]
     */
    protected $productOptionTypes;

    /**
     * Price types
     *
     * @var string[]
     */
    protected $priceTypes;

    /**
     * @param \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig
     * @param \Magento\Catalog\Model\Config\Source\Product\Options\Price $priceConfig
     */
    public function __construct(
        \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig,
        \Magento\Catalog\Model\Config\Source\Product\Options\Price $priceConfig
    ) {
        foreach ($productOptionConfig->getAll() as $option) {
            foreach ($option['types'] as $type) {
                $this->productOptionTypes[] = $type['name'];
            }
        }

        foreach ($priceConfig->toOptionArray() as $item) {
            $this->priceTypes[] = $item['value'];
        }
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
     * @throws Zend_Validate_Exception If validation of $value is impossible
     */
    public function isValid($value)
    {
        $messages = array();

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
        if ($this->isEmpty($title)) {
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
        return $this->isInRange($option->getPriceType(), $this->priceTypes) && !$this->isNegative($option->getPrice());
    }

    /**
     * Check whether value is empty
     *
     * @param mixed $value
     * @return bool
     */
    protected function isEmpty($value)
    {
        return empty($value);
    }

    /**
     * Check whether value is in range
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
     * Check whether value is not negative
     *
     * @param string $value
     * @return bool
     */
    protected function isNegative($value)
    {
        return intval($value) < 0;
    }
}
