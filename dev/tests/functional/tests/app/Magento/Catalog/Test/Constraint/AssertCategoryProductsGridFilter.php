<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that category products grid filter works correctly.
 */
class AssertCategoryProductsGridFilter extends AbstractConstraint
{
    /**
     * Grid columns for tests
     *
     * @var array
     */
    private $testFilterColumns = [
        'visibility',
    ];
    
    /**
     * Assert that category products grid filter works correctly.
     *
     * @param CatalogCategoryIndex $catalogCategoryIndex
     * @param CatalogCategoryEdit $catalogCategoryEdit
     * @param Category $category
     * @return void
     */
    public function processAssert(
        CatalogCategoryIndex $catalogCategoryIndex,
        CatalogCategoryEdit $catalogCategoryEdit,
        Category $category
    ) {
        $catalogCategoryIndex->getTreeCategories()->selectCategory($category, true);
        $categoryProducts = $category->getDataFieldConfig('category_products')['source']->getProducts();
        $catalogCategoryEdit->getEditForm()->openSection('category_products');
        
        foreach ($this->testFilterColumns as $field) {
            $this->testGridFilter($categoryProducts, $catalogCategoryEdit, $field);
        }
    }

    /**
     * @param array $categoryProducts
     * @param CatalogCategoryEdit $catalogCategoryEdit
     * @param string $filterField
     */
    private function testGridFilter(array $categoryProducts, CatalogCategoryEdit $catalogCategoryEdit, $filterField)
    {
        $productsByFilter = [];
        foreach ($categoryProducts as $product) {
            $filterValue = $product->getData($filterField);
            if (!isset($productsByFilter[$filterValue])) {
                $productsByFilter[$filterValue] = [];
            }
            $productsByFilter[$filterValue][] = $product;
        }

        $productsFieldset = $catalogCategoryEdit->getEditForm()->getSection('category_products');
        foreach ($productsByFilter as $filterValue => $products) {
            $productsFieldset->getProductGrid()->search([
                'in_category' => 'Yes',
                $filterField => $filterValue,
            ]);

            $expectedRows = [];
            foreach ($products as $product) {
                $expectedRows[] = $product->getName();
            }
            $gridRows = $productsFieldset->getProductGrid()->getRowsData(['name']);
            $actualRows = array_column($gridRows, 'name');
            sort($expectedRows);
            sort($actualRows);

            \PHPUnit_Framework_Assert::assertEquals(
                $expectedRows,
                $actualRows,
                "Category products grid filter '$filterField' does not work correctly"
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category products grid filter works correctly';
    }
}
