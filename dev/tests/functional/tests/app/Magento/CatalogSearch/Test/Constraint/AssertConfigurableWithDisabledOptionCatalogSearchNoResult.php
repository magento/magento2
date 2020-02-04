<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\CatalogSearch\Test\Page\AdvancedResult;
use Magento\CatalogSearch\Test\Page\CatalogsearchResult;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Assert search has no results after disabling configurable children.
 */
class AssertConfigurableWithDisabledOptionCatalogSearchNoResult extends AbstractConstraint
{
    /**
     * Assert search has no results and product list in absent after disabling configurable children.
     *
     * @param CatalogSearchQuery $catalogSearch
     * @param CatalogsearchResult $catalogsearchResult
     * @param FixtureFactory $fixtureFactory
     * @param CmsIndex $cmsIndex
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $editProductPage
     * @param string|null $queryLength
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function processAssert(
        CatalogSearchQuery $catalogSearch,
        AdvancedResult $resultPage,
        FixtureFactory $fixtureFactory,
        CmsIndex $cmsIndex,
        CatalogProductIndex $productGrid,
        CatalogProductEdit $editProductPage,
        $queryLength = null
    ) {
        /** @var ConfigurableProduct $product */
        $product = $catalogSearch->getDataFieldConfig('query_text')['source']->getFirstProduct();

        $matrix = isset($product->getConfigurableAttributesData()['matrix']) ?
            $product->getConfigurableAttributesData()['matrix'] :
            [];

        foreach ($matrix as $option) {
            $product = $fixtureFactory->createByCode('catalogProductSimple', ['data' => ['status' => 'No']]);
            $filter = ['sku' => $option['sku']];
            $productGrid->open();
            $productGrid->getProductGrid()->searchAndOpen($filter);
            $editProductPage->getProductForm()->fill($product);
            $editProductPage->getFormPageActions()->save();
        }

        $cmsIndex->open();
        $cmsIndex->getSearchBlock()->search($catalogSearch->getQueryText(), $queryLength);

        do {
            $isProductVisible = $resultPage->getListProductBlock()->getProductItem($product)->isVisible();
        } while (!$isProductVisible && $resultPage->getBottomToolbar()->nextPage());

        \PHPUnit\Framework\Assert::assertFalse(
            $isProductVisible,
            "A product with name '" . $product->getName() . "' was found."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Search result has not been found.';
    }
}
