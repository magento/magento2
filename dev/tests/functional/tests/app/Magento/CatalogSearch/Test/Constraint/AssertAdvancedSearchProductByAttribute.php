<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\CatalogSearch\Test\Page\AdvancedSearch;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\CatalogSearch\Test\Page\CatalogsearchResult;

/**
 * Assert that product attribute is searchable on Frontend.
 */
class AssertAdvancedSearchProductByAttribute extends AbstractConstraint
{
    /**
     * Factory for fixtures.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Assert that product attribute is searchable on Frontend.
     *
     * @param CmsIndex $cmsIndex
     * @param InjectableFixture $product
     * @param AdvancedSearch $searchPage
     * @param CatalogsearchResult $catalogSearchResult
     * @param FixtureFactory $fixtureFactory
     * @param int|null $attributeValue
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        InjectableFixture $product,
        AdvancedSearch $searchPage,
        CatalogsearchResult $catalogSearchResult,
        FixtureFactory $fixtureFactory,
        $attributeValue = null
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $cmsIndex->open();
        $cmsIndex->getFooterBlock()->openAdvancedSearch();
        $searchForm = $searchPage->getForm();
        $productSearch = $this->prepareFixture($product, $attributeValue);

        $searchForm->fill($productSearch);
        $searchForm->submit();
        do {
            $isVisible = $catalogSearchResult->getListProductBlock()->getProductItem($product)->isVisible();
        } while (!$isVisible && $catalogSearchResult->getBottomToolbar()->nextPage());

        \PHPUnit\Framework\Assert::assertTrue($isVisible, 'Product attribute is not searchable on Frontend.');
    }

    /**
     * Preparation of fixture data before comparing.
     *
     * @param InjectableFixture $productSearch
     * @param int|null $attributeValue
     * @return CatalogProductSimple
     */
    protected function prepareFixture(InjectableFixture $productSearch, $attributeValue)
    {
        $customAttribute = $productSearch->getDataFieldConfig('custom_attribute')['source']->getAttribute();
        if ($attributeValue !== null) {
            $customAttribute = ['value' => $attributeValue, 'attribute' => $customAttribute];
        }
        return $this->fixtureFactory->createByCode(
            'catalogProductSimple',
            ['data' => ['custom_attribute' => $customAttribute]]
        );
    }

    /**
     * Returns string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product attribute is searchable on Frontend.';
    }
}
