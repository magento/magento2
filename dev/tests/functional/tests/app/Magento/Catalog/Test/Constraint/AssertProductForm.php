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

namespace Magento\Catalog\Test\Constraint;

use Mtf\Fixture\FixtureInterface;
use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;

/**
 * Class AssertProductForm
 */
class AssertProductForm extends AbstractConstraint
{
    /**
     * Formatting options for numeric values
     *
     * @var array
     */
    protected $formattingOptions = [
        'price' => [
            'decimals' => 2,
            'dec_point' => '.',
            'thousands_sep' => ''
        ],
        'qty' => [
            'decimals' => 4,
            'dec_point' => '.',
            'thousands_sep' => ''
        ],
        'weight' => [
            'decimals' => 4,
            'dec_point' => '.',
            'thousands_sep' => ''
        ]
    ];

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert form data equals fixture data
     *
     * @param FixtureInterface $product
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $productPage
     * @return void
     */
    public function processAssert(
        FixtureInterface $product,
        CatalogProductIndex $productGrid,
        CatalogProductEdit $productPage
    ) {
        $filter = ['sku' => $product->getSku()];
        $productGrid->open()->getProductGrid()->searchAndOpen($filter);

        $formData = $productPage->getForm()->getData($product);
        $fixtureData = $this->prepareFixtureData($product);

        $errors = $this->compareArray($fixtureData, $formData);
        \PHPUnit_Framework_Assert::assertTrue(
            empty($errors),
            "These data must be equal to each other:\n" . implode("\n", $errors)
        );
    }

    /**
     * Prepares and returns data to the fixture, ready for comparison
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function prepareFixtureData(FixtureInterface $product)
    {
        $compareData = $product->getData();
        $compareData = array_filter($compareData);

        array_walk_recursive(
            $compareData,
            function (&$item, $key, $formattingOptions) {
                if (isset($formattingOptions[$key])) {
                    $item = number_format(
                        $item,
                        $formattingOptions[$key]['decimals'],
                        $formattingOptions[$key]['dec_point'],
                        $formattingOptions[$key]['thousands_sep']
                    );
                }
            },
            $this->formattingOptions
        );

        return $compareData;
    }

    /**
     * Comparison of multidimensional arrays
     *
     * @param array $fixtureData
     * @param array $formData
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function compareArray(array $fixtureData, array $formData)
    {
        $errors = [];
        $keysDiff = array_diff(array_keys($formData), array_keys($fixtureData));
        if (!empty($keysDiff)) {
            return ['- fixture data do not correspond to form data in composition.'];
        }

        foreach ($fixtureData as $key => $value) {
            if (is_array($value) && is_array($formData[$key])
                && ($innerErrors = $this->compareArray($value, $formData[$key])) && !empty($innerErrors)
            ) {
                $errors = array_merge($errors, $innerErrors);
            } elseif ($value != $formData[$key]) {
                $fixtureValue = empty($value) ? '<empty-value>' : $value;
                $formValue = empty($formData[$key]) ? '<empty-value>' : $formData[$key];
                $errors = array_merge(
                    $errors,
                    [
                        "error key -> '{$key}' : error value ->  '{$fixtureValue}' does not equal -> '{$formValue}'"
                    ]
                );
            }
        }

        return $errors;
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Form data equal the fixture data.';
    }
}
