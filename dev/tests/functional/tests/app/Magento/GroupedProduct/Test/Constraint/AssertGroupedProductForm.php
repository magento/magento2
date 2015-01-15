<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductForm;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Mtf\Fixture\FixtureInterface;

/**
 * Class AssertGroupedProductForm
 */
class AssertGroupedProductForm extends AssertProductForm
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

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
        $fieldsForm = $productPage->getProductForm()->getData($product);
        $fieldsFixture = $this->prepareFixtureData($product->getData());
        $fieldsFixture['associated'] = $this->prepareGroupedOptions($fieldsFixture['associated']);

        $errors = $this->verifyData($fieldsFixture, $fieldsForm);
        \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);
    }

    /**
     * Prepare Grouped Options array from preset
     *
     * @param array $fields
     * @return array
     */
    protected function prepareGroupedOptions(array $fields)
    {
        $result = [];
        foreach ($fields['assigned_products'] as $key => $item) {
            $result['assigned_products'][$key]['name'] = $item['name'];
            $result['assigned_products'][$key]['qty'] = $item['qty'];
        }

        return $result;
    }
}
