<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

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
     * @return void
     */
    private function testGridFilter(array $categoryProducts, CatalogCategoryEdit $catalogCategoryEdit, $filterField)
    {
        $expectedProducts = [];
        foreach ($categoryProducts as $product) {
            $expectedProducts[$product->getData('name')] = [
                'filter' => $filterField,
                'value' => $product->getData($filterField)
            ];
        }

        $actualProducts = [];
        /** @var \Magento\Catalog\Test\Block\Adminhtml\Category\Edit\CategoryForm $productsFieldset */
        $productsFieldset = $catalogCategoryEdit->getEditForm()->getSection('category_products');
        $gridRows = $productsFieldset->getProductGrid()->getRowsData(['name', $filterField]);
        foreach ($gridRows as $row) {
            $actualProducts[$row['name']] = [
                'filter' => $filterField,
                'value' => $row[$filterField]
            ];
        }

        \PHPUnit\Framework\Assert::assertEquals(
            $expectedProducts,
            $actualProducts,
            "Category products grid filter '$filterField' does not work correctly"
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString() : string
    {
        return 'Category products grid filter works correctly';
    }
}
