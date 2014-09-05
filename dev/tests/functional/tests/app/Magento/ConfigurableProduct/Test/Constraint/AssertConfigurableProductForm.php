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

namespace Magento\ConfigurableProduct\Test\Constraint;

use Mtf\Fixture\FixtureInterface;
use Magento\Catalog\Test\Constraint\AssertProductForm;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;

/**
 * Class AssertConfigurableProductForm
 * Assert that displayed product data on edit page equals passed from fixture
 */
class AssertConfigurableProductForm extends AssertProductForm
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Assert that displayed product data on edit page equals passed from fixture
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
        $productGrid->open()->getProductGrid()->searchAndOpen(['sku' => $product->getSku()]);

        $form = $productPage->getForm();
        $formData = $form->getData($product);
        foreach (array_keys($formData['configurable_attributes_data']['matrix']) as $key) {
            unset($formData['configurable_attributes_data']['matrix'][$key]['price']);
        }

        $fixtureData = $this->prepareFixtureData($product->getData(), $product);
        $attributes = $fixtureData['configurable_attributes_data']['attributes_data'];
        $matrix = $fixtureData['configurable_attributes_data']['matrix'];
        unset($fixtureData['configurable_attributes_data'], $fixtureData['id']);

        $fixtureData['configurable_attributes_data']['attributes_data'] = $this->prepareAttributes($attributes);
        $fixtureData['configurable_attributes_data']['matrix'] = $this->prepareMatrix($matrix);

        $errors = $this->verifyData($fixtureData, $formData);
        \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);
    }

    /**
     * Preparing data attributes fixture
     *
     * @param array $fixtureAttribute
     * @return array
     */
    protected function prepareAttributes(array $fixtureAttribute)
    {
        foreach ($fixtureAttribute as &$attribute) {
            unset($attribute['id'], $attribute['label'], $attribute['code']);
            foreach ($attribute['options'] as &$option) {
                $option['pricing_value'] = number_format($option['pricing_value'], 4);
                unset($option['id']);
            }
        }

        return $fixtureAttribute;
    }

    /**
     * Preparing data matrix fixture
     *
     * @param array $fixtureMatrix
     * @return array
     */
    protected function prepareMatrix(array $fixtureMatrix)
    {
        foreach ($fixtureMatrix as &$matrix) {
            $matrix['display'] = 'Yes';
            unset($matrix['configurable_attribute'], $matrix['associated_product_ids']);
        }

        return $fixtureMatrix;
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Form data equal the configurable product data.';
    }
}
