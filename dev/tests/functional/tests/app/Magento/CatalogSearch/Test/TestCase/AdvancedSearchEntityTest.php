<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\CatalogSearch\Test\Page\AdvancedSearch;
use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Fixture\FixtureFactory;
use Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Two specific simple product is created(unique sku,name,short/full description, tax class)
 *
 * Steps:
 * 1. Navigate to Frontend
 * 2. Click "Advanced Search"
 * 3. Fill test data in to field(s)
 * 4. Click "Search" button
 * 5. Perform all asserts
 *
 * @group Search_Frontend_(MX)
 * @ZephyrId MAGETWO-24729
 */
class AdvancedSearchEntityTest extends Injectable
{
    /**
     * Prepare data.
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        /** @var CatalogProductSimple $productSymbols */
        $productSymbols = $fixtureFactory->createByCode(
            'catalogProductSimple',
            ['dataSet' => 'abc_dfj_simple_for_advancedsearch']
        );
        $productSymbols->persist();

        /** @var CatalogProductSimple $productNumbers */
        $productNumbers = $fixtureFactory->createByCode(
            'catalogProductSimple',
            ['dataSet' => 'adc_123_simple_for_advancedsearch']
        );
        $productNumbers->persist();

        return [
            'productsSearch' => [
                'simple_1' => $productSymbols,
                'simple_2' => $productNumbers,
            ]
        ];
    }

    /**
     * Run test creation for advanced search entity.
     *
     * @param CatalogProductSimple $productSearch
     * @param CmsIndex $cmsIndex
     * @param AdvancedSearch $searchPage
     * @return void
     */
    public function test(
        CatalogProductSimple $productSearch,
        CmsIndex $cmsIndex,
        AdvancedSearch $searchPage
    ) {
        $cmsIndex->open();
        $cmsIndex->getSearchBlock()->clickAdvancedSearchButton();
        $searchForm = $searchPage->getForm();
        $searchForm->fill($productSearch);
        $searchForm->submit();
    }
}
