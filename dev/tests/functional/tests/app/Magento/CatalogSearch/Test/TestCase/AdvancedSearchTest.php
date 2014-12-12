<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\CatalogSearch\Test\TestCase;

use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;

/**
 * Class AdvancedSearchTest
 * Searching product in the Frontend via advanced search
 */
class AdvancedSearchTest extends Functional
{
    /**
     * Advanced search product on frontend by product name
     *
     * @ZephyrId MAGETWO-12421
     */
    public function testProductSearch()
    {
        //Data
        $productFixture = Factory::getFixtureFactory()->getMagentoCatalogSimpleProduct();
        $productFixture->switchData('simple');
        $productFixture->persist();

        //Pages
        $homePage = Factory::getPageFactory()->getCmsIndexIndex();
        $advancedSearchPage = Factory::getPageFactory()->getCatalogsearchAdvanced();
        $advancedSearchResultPage = Factory::getPageFactory()->getCatalogsearchResult();

        //Steps
        $homePage->open();
        $homePage->getSearchBlock()->clickAdvancedSearchButton();
        $searchForm = $advancedSearchPage->getForm();
        $this->assertTrue($searchForm->isVisible(), '"Advanced Search" form is not opened');
        $searchForm->fillCustom($productFixture, ['name', 'sku']);
        $searchForm->submit();

        //Verifying
        $productName = $productFixture->getName();
        $this->assertTrue(
            $advancedSearchResultPage->getListProductBlock()->isProductVisible($productName),
            sprintf('Product "%s" is not displayed on the "Catalog Advanced Search" results page."', $productName)
        );
    }
}
