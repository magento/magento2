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

namespace Magento\CatalogSearch\Test\TestCase;

use Mtf\TestCase\Injectable;
use Mtf\Fixture\FixtureFactory;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\CatalogSearch\Test\Page\AdvancedSearch;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Test Creation for AdvancedSearchEntity
 *
 * Test Flow:
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
     * Prepare data
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
                'simple_2' => $productNumbers
            ]
        ];
    }

    /**
     * Run test creation for advanced search entity
     *
     * @param array $products
     * @param CatalogProductSimple $productSearch
     * @param CmsIndex $cmsIndex
     * @param AdvancedSearch $searchPage
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testSearch(
        array $products,
        CatalogProductSimple $productSearch,
        CmsIndex $cmsIndex,
        AdvancedSearch $searchPage
    ) {
        $this->markTestIncomplete('MAGETWO-27664');
        $cmsIndex->open();
        $cmsIndex->getSearchBlock()->clickAdvancedSearchButton();
        $searchForm = $searchPage->getForm();
        $searchForm->fill($productSearch);
        $searchForm->submit();
    }
}
