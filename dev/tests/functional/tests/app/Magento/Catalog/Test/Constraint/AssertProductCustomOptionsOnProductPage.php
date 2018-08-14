<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductCustomOptionsOnProductPage
 */
class AssertProductCustomOptionsOnProductPage extends AbstractAssertForm
{
    /**
     * Skipped field for custom options
     *
     * @var array
     */
    protected $skippedFieldOptions = [
        'Text/Field' => [
            'price_type',
            'sku',
        ],
        'Text/Area' => [
            'price_type',
            'sku',
        ],
        'Select/Drop-down' => [
            'price_type',
            'sku',
        ],
        'File/File' => [
            'price_type',
            'sku',
        ],
        'Select/Radio Buttons' => [
            'price_type',
            'sku',
        ],
        'Select/Checkbox' => [
            'price_type',
            'sku',
        ],
        'Select/Multiple Select' => [
            'price_type',
            'sku',
        ],
        'Date/Date' => [
            'price_type',
            'sku',
        ],
        'Date/Date & Time' => [
            'price_type',
            'sku',
        ],
        'Date/Time' => [
            'price_type',
            'sku',
        ],
    ];

    /**
     * Flag for verify price data
     *
     * @var bool
     */
    protected $isPrice = true;

    /**
     * Assertion that commodity options are displayed correctly
     *
     * @param CatalogProductView $catalogProductView
     * @param FixtureInterface $product
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        FixtureInterface $product,
        BrowserInterface $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $actualPrice = null;
        if ($this->isPrice) {
            $priceBlock = $catalogProductView->getViewBlock()->getPriceBlock();
            $specialPrice = $priceBlock->getSpecialPrice();
            $price = $priceBlock->getPrice();
            $actualPrice = $specialPrice ? $specialPrice : $price;
        }
        $fixtureCustomOptions = $this->prepareOptions($product, $actualPrice);
        $formCustomOptions = $catalogProductView->getViewBlock()->getOptions($product)['custom_options'];
        $error = $this->verifyData($fixtureCustomOptions, $formCustomOptions);
        \PHPUnit_Framework_Assert::assertEmpty($error, $error);
    }

    /**
     * Preparation options before comparing
     *
     * @param FixtureInterface $product
     * @param int|null $actualPrice
     * @return array
     */
    protected function prepareOptions(FixtureInterface $product, $actualPrice = null)
    {
        $result = [];
        $customOptions = $product->hasData('custom_options')
            ? $product->getDataFieldConfig('custom_options')['source']->getCustomOptions()
            : [];
        $actualPrice = $actualPrice ?: $product->getPrice();
        foreach ($customOptions as $customOption) {
            $result = $this->prepareEachCustomOption($actualPrice, $customOption, $result);
        }
        return $result;
    }

    /**
     * Verify fixture and form data
     *
     * @param array $fixtureData
     * @param array $formData
     * @param bool $isStrict
     * @param bool $isPrepareError
     * @return array|string
     */
    protected function verifyData(array $fixtureData, array $formData, $isStrict = false, $isPrepareError = true)
    {
        $errors = [];
        foreach ($fixtureData as $key => $value) {
            if (in_array($key, $this->skippedFields, true)) {
                continue;
            }
            $formValue = isset($formData[$key]) ? $formData[$key] : null;
            $errors = $this->verifyDataForErrors($formValue, $key, $errors, $value);
        }
        return $this->prepareErrorsForOutput($fixtureData, $formData, $isStrict, $isPrepareError, $errors);
    }

    /**
     * Checks data for not equal values error
     *
     * @param array|string $value
     * @param array|string $formValue
     * @param string $key
     * @return string
     */
    private function checkNotEqualValuesErrors($value, $formValue, $key)
    {
        /**
         * It is needed because sorting in db begins from 1, but when selenium driver gets value from form it starts
         * calculate from 0. So this operation checks this case
         */
        if ((int)$value === (int)$formValue + 1) {
            return '';
        }
        if (is_array($value)) {
            $value = $this->arrayToString($value);
        }
        if (is_array($formValue)) {
            $formValue = $this->arrayToString($formValue);
        }
        return sprintf('- %s: "%s" instead of "%s"', $key, $formValue, $value);
    }

    /**
     * Prepare errors data to output
     *
     * @param array $fixtureData
     * @param array $formData
     * @param $isStrict
     * @param $isPrepareError
     * @param $errors
     * @return array|string
     */
    private function prepareErrorsForOutput(array $fixtureData, array $formData, $isStrict, $isPrepareError, $errors)
    {
        if ($isStrict) {
            $diffData = array_diff(array_keys($formData), array_keys($fixtureData));
            if ($diffData) {
                $errors[] = '- fields ' . implode(', ', $diffData) . ' is absent in fixture';
            }
        }
        if ($isPrepareError) {
            return $this->prepareErrors($errors);
        }
        return $errors;
    }

    /**
     * Checks data for errors
     *
     * @param array|string $formValue
     * @param string $key
     * @param array $errors
     * @param string $value
     * @return array
     */
    private function verifyDataForErrors($formValue, $key, $errors, $value)
    {
        if (is_numeric($formValue)) {
            $formValue = (float)$formValue;
        }
        if (null === $formValue) {
            $errors[] = '- field "' . $key . '" is absent in form';
        } elseif (is_array($value) && is_array($formValue)) {
            $valueErrors = $this->verifyData($value, $formValue, true, false);
            if (!empty($valueErrors)) {
                $errors[$key] = $valueErrors;
            }
        } elseif ($value != $formValue) {
            $notEqualValuesErrors = $this->checkNotEqualValuesErrors($value, $formValue, $key);
            if ($notEqualValuesErrors) {
                $errors[] = $notEqualValuesErrors;
            }
        }
        return $errors;
    }

    /**
     * @param $actualPrice
     * @param $customOption
     * @param $result
     * @return array
     */
    private function prepareEachCustomOption($actualPrice, $customOption, $result)
    {
        $skippedField = isset($this->skippedFieldOptions[$customOption['type']])
            ? $this->skippedFieldOptions[$customOption['type']]
            : [];
        foreach ($customOption['options'] as &$option) {
            // recalculate percent price
            if ('Percent' == $option['price_type']) {
                $option['price'] = ($actualPrice * $option['price']) / 100;
                $option['price'] = round($option['price'], 2);
            }

            $option = array_diff_key($option, array_flip($skippedField));
        }
        $result[$customOption['title']] = $customOption;
        return $result;
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Value of custom option on the page is correct.';
    }
}
