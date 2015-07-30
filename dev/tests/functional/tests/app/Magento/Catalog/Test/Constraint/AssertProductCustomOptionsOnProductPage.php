<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function prepareOptions(FixtureInterface $product, $actualPrice = null)
    {
        $result = [];
        $customOptions = $product->hasData('custom_options')
            ? $product->getDataFieldConfig('custom_options')['source']->getCustomOptions()
            : null;
        $actualPrice = $actualPrice ? $actualPrice : $product->getPrice();
        foreach ($customOptions as $customOption) {
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
        }

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
